// Dashboard loader: consume los endpoints que añadimos en backend/routes/api.php
const BASE_URL = document.querySelector('meta[name="base-url"]')?.content || "";
async function loadDashboard() {
  try {
    const [completoRes, obrasRes, finRes] = await Promise.all([
      fetch("../../../backend/public/index.php/api/dashboard/completo", {
        credentials: "include",
      }),
      fetch("../../../backend/public/index.php/api/dashboard/obras", {
        credentials: "include",
      }),
      fetch("../../..//backend/public/index.php/api/dashboard/financiero", {
        credentials: "include",
      }),
    ]);

    let completo = completoRes.ok ? await completoRes.json() : null;
    let obras = obrasRes.ok ? await obrasRes.json() : null;
    let fin = finRes.ok ? await finRes.json() : null;

    // Normalizar respuestas: si vienen como array de 1 elemento, usar el objeto.
    function normalize(obj) {
      if (Array.isArray(obj)) {
        if (obj.length === 1) return obj[0];
        return obj; // dejar arrays de varios elementos
      }
      if (obj && typeof obj === "object") {
        // intentar decodificar campos que vienen como JSON string
        Object.keys(obj).forEach((k) => {
          const v = obj[k];
          if (typeof v === "string") {
            const t = v.trim();
            if (t.startsWith("{") || t.startsWith("[")) {
              try {
                obj[k] = JSON.parse(v);
              } catch (e) {
                // no parseable, dejar el string
              }
            }
          }
        });
      }
      return obj;
    }

    // normalizar y desplegar campo `resultado` cuando exista
    function unwrap(obj) {
      if (!obj || typeof obj !== "object") return obj;
      if (obj.resultado !== undefined) return obj.resultado;
      return obj;
    }

    // primero desempaquetar 'resultado' y luego normalizar su contenido
    completo = normalize(unwrap(completo));
    obras = normalize(unwrap(obras));
    fin = normalize(unwrap(fin));

    // KPIs (más robustos: probar múltiples ubicaciones y nombres de campo)
    try {
      const elTotal = document.getElementById("kpi_total_obras");
      const elEnCurso = document.getElementById("kpi_en_curso");
      const elObreros = document.getElementById("kpi_obreros");
      const elPres = document.getElementById("kpi_presupuesto");

      // total obras: buscar en varias rutas posibles
      let totalObras = undefined;
      if (obras) {
        totalObras =
          obras.total_obras ?? obras.total ?? obras.cantidad ?? obras.count;
        if (typeof obras === "number") totalObras = obras;
        if (Array.isArray(obras) && obras.length) totalObras = obras.length;
      }
      if ((totalObras === undefined || totalObras === null) && completo) {
        totalObras =
          completo.total_obras ??
          completo.metricas_generales?.total_obras ??
          completo.obras_total ??
          null;
      }
      if (elTotal) {
        if (
          totalObras !== undefined &&
          totalObras !== null &&
          !Number.isNaN(Number(totalObras))
        ) {
          elTotal.textContent = new Intl.NumberFormat("es-VE").format(
            Number(totalObras),
          );
        } else {
          elTotal.textContent = "—";
        }
      }

      // en curso (fallbacks: campos directos o contar obras en curso)
      let enCurso = undefined;
      enCurso =
        obras?.en_curso ??
        obras?.enCurso ??
        completo?.metricas_generales?.en_curso ??
        completo?.en_curso ??
        null;
      // Si no viene explícito, inferir por conteo de obras en curso
      if ((enCurso === undefined || enCurso === null) && completo) {
        if (Array.isArray(completo.obras_en_curso))
          enCurso = completo.obras_en_curso.length;
        else if (Array.isArray(completo.obras_en_curso_paged))
          enCurso = completo.obras_en_curso_paged.length;
        else if (
          completo.obras_en_curso_pagination &&
          completo.obras_en_curso_pagination.total !== undefined
        )
          enCurso = completo.obras_en_curso_pagination.total;
      }
      if (elEnCurso) {
        if (
          enCurso !== undefined &&
          enCurso !== null &&
          !Number.isNaN(Number(enCurso))
        ) {
          elEnCurso.textContent = new Intl.NumberFormat("es-VE").format(
            Number(enCurso),
          );
        } else {
          elEnCurso.textContent = "—";
        }
      }

      // obreros (ya con varios fallback)
      let obrerosVal = "—";
      if (completo && completo.metricas_generales) {
        const mg = completo.metricas_generales;
        obrerosVal =
          mg.total_obreros ??
          mg.obreros?.total_registrados ??
          mg.obreros ??
          mg.total_obreros_registrados ??
          "—";
      }
      if (elObreros) {
        if (
          obrerosVal !== undefined &&
          obrerosVal !== null &&
          !Number.isNaN(Number(obrerosVal))
        ) {
          elObreros.textContent = new Intl.NumberFormat("es-VE").format(
            Number(obrerosVal),
          );
        } else {
          elObreros.textContent = obrerosVal ?? "—";
        }
      }

      // presupuesto (buscar en varias ubicaciones; si viene como array, sumar presupuestos)
      let pres =
        obras?.presupuesto_total ??
        obras?.presupuesto ??
        completo?.metricas_generales?.presupuesto_total ??
        null;
      let presSource = null;

      const parseNumber = (v) => {
        if (v === null || v === undefined) return NaN;
        if (typeof v === "number") return v;
        const s = String(v)
          .replace(/[^0-9.,-]/g, "")
          .replace(/\./g, "")
          .replace(",", ".");
        const n = Number(s);
        return Number.isFinite(n) ? n : NaN;
      };

      const findFieldByPattern = (
        obj,
        patterns = ["presup", "monto", "budget", "total"],
      ) => {
        if (!obj || typeof obj !== "object") return null;
        for (const k of Object.keys(obj)) {
          for (const p of patterns) {
            if (new RegExp(p, "i").test(k)) {
              const val = obj[k];
              const num = parseNumber(val);
              if (!Number.isNaN(num)) return { value: num, key: k };
            }
          }
        }
        return null;
      };

      // Si `obras` es array, intentar sumar presupuestos por obra
      if (
        (pres === undefined || pres === null) &&
        Array.isArray(obras) &&
        obras.length
      ) {
        const sum = obras.reduce((acc, o) => {
          const cand = o?.presupuesto_total ?? o?.presupuesto ?? o?.monto ?? 0;
          const num = parseNumber(cand);
          return acc + (Number.isFinite(num) ? num : 0);
        }, 0);
        if (sum > 0) {
          pres = sum;
          presSource = "sum(obras)";
        }
      }

      // intentar detectar campos numericos relacionados con presupuesto en objetos simples
      if (
        (pres === undefined || pres === null) &&
        obras &&
        typeof obras === "object" &&
        !Array.isArray(obras)
      ) {
        const found = findFieldByPattern(obras);
        if (found) {
          pres = found.value;
          presSource = `obras.${found.key}`;
        }
      }

      if (
        (pres === undefined || pres === null) &&
        completo &&
        completo.metricas_generales
      ) {
        const found = findFieldByPattern(completo.metricas_generales);
        if (found) {
          pres = found.value;
          presSource = `completo.metricas_generales.${found.key}`;
        }
      }

      // fallback desde respuesta financiero
      if ((pres === null || pres === undefined) && fin && fin.resumen) {
        const found = findFieldByPattern(fin.resumen);
        if (found) {
          pres = found.value;
          presSource = `fin.resumen.${found.key}`;
        } else
          pres =
            fin.resumen.total_presupuesto ??
            fin.resumen.presupuesto_total ??
            fin.resumen.presupuesto ??
            pres;
        if (pres !== null && pres !== undefined && !presSource)
          presSource = "fin.resumen";
      }

      // Si viene como string con símbolos, limpiarlo y parsear
      if (pres !== null && pres !== undefined && typeof pres === "string") {
        const parsed = parseNumber(pres);
        if (!Number.isNaN(parsed)) pres = parsed;
      }

      // Si aún no hay presupuesto y completo.metricas_generales tiene campos alternativos (replicado por seguridad)
      if (
        (pres === null || pres === undefined) &&
        completo &&
        completo.metricas_generales
      ) {
        const found = findFieldByPattern(completo.metricas_generales);
        if (found) {
          pres = found.value;
          presSource = `completo.metricas_generales.${found.key}`;
        } else {
          pres =
            completo.metricas_generales.presupuesto_total ??
            completo.metricas_generales.presupuesto ??
            null;
          if (pres !== null && pres !== undefined && !presSource)
            presSource = "completo.metricas_generales";
        }
      }

      // Mostrar resultado con formato o fallback
      if (elPres) {
        if (
          pres !== null &&
          pres !== undefined &&
          !Number.isNaN(Number(pres))
        ) {
          elPres.textContent = new Intl.NumberFormat("es-VE", {
            style: "currency",
            currency: "VES",
          }).format(Number(pres));
        } else {
          elPres.textContent = "—";
        }
      }

      // Debug extendido: si no se resolvió presupuesto, mostrar objetos brutos para inspección
      try {
        if (pres === null || pres === undefined) {
          console.debug("KPI presupuesto no encontrado — objetos devueltos:", {
            obras,
            completo,
            fin,
          });
        } else {
          console.debug("KPI presupuesto resuelto", { pres, presSource });
        }
      } catch (e) {}
    } catch (e) {
      console.warn("Error asignando KPIs:", e);
    }

    // Mostrar debug SP info si existe
    try {
      const debugInfoEl = document.getElementById("debugInfo");
      const debugRow = document.getElementById("debugRow");
      const info = {
        completo:
          completo && completo._debug_sp ? completo._debug_sp
          : completo && completo.debug_missing_sp ?
            "missing:" + completo.debug_missing_sp.join(",")
          : null,
        obras:
          obras && obras._debug_sp ? obras._debug_sp
          : obras && obras.debug_missing_sp ?
            "missing:" + obras.debug_missing_sp.join(",")
          : null,
        financiero:
          fin && fin._debug_sp ? fin._debug_sp
          : fin && fin.debug_missing_sp ?
            "missing:" + fin.debug_missing_sp.join(",")
          : null,
      };
      const lines = [];
      for (const k of ["completo", "obras", "financiero"]) {
        const v =
          info[
            k === "financiero" ? "financiero"
            : k === "obras" ? "obras"
            : "completo"
          ];
        lines.push(`${k}: ${v ?? "n/a"}`);
      }
      if (debugInfoEl) debugInfoEl.textContent = lines.join(" | ");
      if (debugRow) debugRow.style.display = "block";
    } catch (e) {
      // ignore
    }

    // Alertas (renderizadas por categoría: iconos, badges, alerts de Bootstrap)
    const alertList = document.getElementById("alertList");
    alertList.innerHTML = "";
    const alertas =
      completo && completo.alertas ? completo.alertas
      : obras && obras.alertas_importantes ? obras.alertas_importantes
      : [];

    const mapSeverity = (a) => {
      const s = (a.severity || a.level || a.tipo || "")
        .toString()
        .toLowerCase();
      if (/crit|error|danger|peligro|urgente/.test(s))
        return { bs: "danger", icon: "error" };
      if (/warn|advert|advertencia|precaucion|warning/.test(s))
        return { bs: "warning", icon: "warning" };
      if (/ok|success|exito|correcto/.test(s))
        return { bs: "success", icon: "check_circle" };
      if (/info|informacion|notice/.test(s))
        return { bs: "info", icon: "info" };
      return { bs: "secondary", icon: "notification_important" };
    };

    if (!alertas || alertas.length === 0) {
      alertList.textContent = "Sin alertas importantes.";
    } else {
      // Usar contenedor vertical de alerts
      const container = document.createElement("div");
      container.className = "d-flex flex-column gap-2";

      alertas.forEach((a) => {
        const meta = mapSeverity(a);
        const alertDiv = document.createElement("div");
        alertDiv.className = `alert alert-${meta.bs} d-flex align-items-start gap-2 mb-0`;
        alertDiv.setAttribute("role", "alert");

        const icon = document.createElement("span");
        icon.className = "material-icons";
        icon.setAttribute("aria-hidden", "true");
        icon.style.fontSize = "1.25rem";
        icon.textContent = meta.icon;

        const body = document.createElement("div");
        body.style.flex = "1";

        const header = document.createElement("div");
        header.className = "d-flex align-items-center gap-2";
        const title = document.createElement("strong");
        title.textContent = a.tipo || a.title || "Alerta";
        const date = document.createElement("small");
        date.className = "text-muted";
        date.style.marginLeft = "0.4rem";
        date.textContent = a.fecha ? ` ${a.fecha}` : "";

        header.appendChild(title);
        header.appendChild(date);

        // badge con nivel o etiqueta corta
        const badgeText = a.level || a.severity || a.tag || null;
        if (badgeText) {
          const spanBadge = document.createElement("span");
          spanBadge.className = `badge bg-${meta.bs} text-white ms-2`;
          spanBadge.style.fontSize = "0.65rem";
          spanBadge.textContent = badgeText;
          header.appendChild(spanBadge);
        }

        const message = document.createElement("div");
        message.className = "mt-1";
        message.innerHTML = a.mensaje ?? a.message ?? "";

        body.appendChild(header);
        body.appendChild(message);

        alertDiv.appendChild(icon);
        alertDiv.appendChild(body);

        container.appendChild(alertDiv);
      });

      alertList.appendChild(container);
    }

    // Obras en curso (lista paginada)
    const obrasList = document.getElementById("obrasList");
    obrasList.innerHTML = "";
    const obrasPaged =
      completo && completo.obras_en_curso_paged ? completo.obras_en_curso_paged
      : completo && completo.obras_en_curso ? completo.obras_en_curso
      : [];
    const obrasPager =
      completo && completo.obras_en_curso_pagination ?
        completo.obras_en_curso_pagination
      : null;
    if (Array.isArray(obrasPaged) && obrasPaged.length) {
      const table = document.createElement("table");
      table.className = "table table-sm";
      const tbody = document.createElement("tbody");
      obrasPaged.forEach((o) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td><strong>${o.nombre}</strong><br/><small class="text-muted">Responsable: ${o.responsable || "—"} • Progreso: ${o.progreso ?? "—"}%</small></td>`;
        tbody.appendChild(tr);
      });
      table.appendChild(tbody);
      obrasList.appendChild(table);

      // pager
      if (obrasPager) {
        const pager = document.createElement("div");
        pager.className = "d-flex align-items-center gap-2";
        const info = document.createElement("div");
        info.className = "text-muted small";
        info.textContent = `Página ${obrasPager.page} / ${obrasPager.total_pages} — ${obrasPager.total} obras`;
        const btnPrev = document.createElement("button");
        btnPrev.className = "btn btn-sm btn-light";
        btnPrev.textContent = "Anterior";
        btnPrev.disabled = obrasPager.page <= 1;
        const btnNext = document.createElement("button");
        btnNext.className = "btn btn-sm btn-light";
        btnNext.textContent = "Siguiente";
        btnNext.disabled = obrasPager.page >= obrasPager.total_pages;
        btnPrev.addEventListener("click", () =>
          loadCompletoPage(obrasPager.page - 1),
        );
        btnNext.addEventListener("click", () =>
          loadCompletoPage(obrasPager.page + 1),
        );
        pager.appendChild(btnPrev);
        pager.appendChild(btnNext);
        pager.appendChild(info);
        obrasList.appendChild(pager);
      }
    } else {
      obrasList.textContent = "No hay obras en curso listadas.";
    }

    // Financiero resumen + gráfico
    const finEl = document.getElementById("financieroSummary");
    if (fin && fin.resumen) {
      finEl.innerHTML = `
            <div>Gastos totales: <strong>${new Intl.NumberFormat("es-VE", { style: "currency", currency: "VES" }).format(fin.resumen.total_gastos)}</strong></div>
            <div>Pagos totales: <strong>${new Intl.NumberFormat("es-VE", { style: "currency", currency: "VES" }).format(fin.resumen.total_pagos)}</strong></div>
          `;
      // draw chart if gastos_por_mes available
      try {
        let gastos = [];
        if (fin && fin.gastos_por_mes) {
          if (Array.isArray(fin.gastos_por_mes)) {
            gastos = fin.gastos_por_mes;
          } else if (typeof fin.gastos_por_mes === "string") {
            try {
              gastos = JSON.parse(fin.gastos_por_mes);
            } catch (e) {
              gastos = [];
            }
          }
        }

        const canvas = document.getElementById("chartFinanciero");
        if (canvas && canvas.getContext) {
          const ctx = canvas.getContext("2d");
          const labels = (gastos || []).map((g) => "Mes " + (g.mes || "?"));
          const data = (gastos || []).map((g) => Number(g.gastos || 0));
          if (window._chartFin) window._chartFin.destroy();
          window._chartFin = new Chart(ctx, {
            type: "bar",
            data: {
              labels: labels,
              datasets: [
                {
                  label: "Gastos",
                  data: data,
                  backgroundColor: "rgba(160,107,154,0.9)",
                },
              ],
            },
            options: { responsive: true, maintainAspectRatio: false },
          });
        } else {
          console.warn("Chart canvas not found or unsupported");
        }
      } catch (e) {
        console.warn("Chart render failed", e);
      }
    } else if (
      completo &&
      completo.metricas_generales &&
      completo.metricas_generales.financiero
    ) {
      const f = completo.metricas_generales.financiero;
      finEl.innerHTML = `<div>Gastos totales: <strong>${f.gastos_totales}</strong></div>`;
    } else {
      finEl.textContent = "Información financiera no disponible.";
    }
  } catch (err) {
    console.error("Error cargando dashboard", err);
  }
}

document.addEventListener("DOMContentLoaded", loadDashboard);
