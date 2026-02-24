document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  const card = document.querySelector(".login-card");
  const notice = document.querySelector(".notice");
  const eye = document.querySelector(".eye");
  const pass = document.getElementById("pass");

  // Base URL desde meta y CSRF helper (disponible globalmente para handlers)
  const BASE_URL =
    document.querySelector('meta[name="base-url"]')?.content || "";
  let CSRF_TOKEN = null;

  async function fetchCsrfToken() {
    if (CSRF_TOKEN) return CSRF_TOKEN;
    try {
      const resp = await fetch(
        (BASE_URL || "..") + "/backend/public/index.php/api/csrf-token",
        { credentials: "include" },
      );
      if (!resp.ok) return null;
      const j = await resp.json();
      CSRF_TOKEN = j.token || null;
      return CSRF_TOKEN;
    } catch (e) {
      return null;
    }
  }

  // Helper to show notices with styles and ARIA
  function showNotice(type, message, timeout = 4000) {
    if (!notice) return;
    notice.className = "notice" + (type ? " notice--" + type : "");
    // role for critical messages
    if (type === "error" || type === "warn")
      notice.setAttribute("role", "alert");
    else notice.setAttribute("role", "status");
    const icon =
      {
        success: "✅",
        error: "⚠️",
        info: "ℹ️",
        warn: "⚠️",
      }[type] || "";

    // spinner for info/loading
    if (type === "info" && /cargando|comprobando|esperando/i.test(message)) {
      notice.innerHTML = `<span class="notice__icon"><span class="spinner"></span></span><span class="notice__text">${message}</span>`;
    } else {
      notice.innerHTML = `<span class="notice__icon">${icon}</span><span class="notice__text">${message}</span>`;
    }

    if (timeout > 0) {
      clearTimeout(notice._timeout);
      notice._timeout = setTimeout(() => {
        if (notice) {
          notice.className = "notice";
          notice.innerHTML = "";
          notice.removeAttribute("role");
        }
      }, timeout);
    }
  }

  // Toggle password visibility
  eye?.addEventListener("click", () => {
    if (pass.type === "password") {
      pass.type = "text";
      eye.textContent = "🙈";
    } else {
      pass.type = "password";
      eye.textContent = "👁️";
    }
    pass.focus();
  });

  // Fake authentication (replace with real call later)
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const user = form.user.value.trim();
    const pwd = form.pass.value;
    showNotice("", "");
    card.classList.remove("error", "success");
    card.classList.add("loading");
    const submitBtn = document.getElementById("submitBtn");
    submitBtn.disabled = true;

    // small loading notice
    showNotice("info", "Comprobando credenciales…", 0);

    try {
      // small artificial delay to show animation
      await new Promise((res) => setTimeout(res, 300));

      // Simple validation
      if (!user || !pwd) throw new Error("Completa usuario y contraseña");

      // Enviar credenciales al backend (contraseña en texto; backend verifica bcrypt)
      const token = await fetchCsrfToken();
      const resp = await fetch(
        (BASE_URL || "..") + "/backend/public/index.php/api/login",
        {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
            ...(token ? { "X-CSRF-Token": token } : {}),
          },
          body: JSON.stringify({ username: user, password: pwd }),
        },
      );

      const data = await resp.json();

      // Manejo específico de códigos HTTP
      if (resp.status === 403) {
        throw new Error(data.mensaje || "Cuenta bloqueada");
      }

      // Si el backend indica que el usuario requiere verificación (p.ej. 2FA o verificación adicional)
      if (data.requiere_verificacion) {
        card.classList.remove("loading");
        card.classList.add("error");
        showNotice(
          "warn",
          data.mensaje || "Usuario requiere verificación",
          8000,
        );
        submitBtn.disabled = false;
        return;
      }

      // Respuesta puede tener diferentes formas según el backend; normalizamos
      const isSuccess = Boolean(
        data.success || (data.usuario && Object.keys(data.usuario).length),
      );

      if (isSuccess) {
        card.classList.remove("loading");
        card.classList.add("success");
        showNotice("success", "¡Autenticación correcta! Redirigiendo...", 1200);

        const overlay = document.createElement("div");
        overlay.className = "page-overlay success";
        overlay.innerHTML = `<div class="center"><h2>¡Ingreso exitoso!</h2></div>`;
        document.body.appendChild(overlay);
        requestAnimationFrame(() => {
          document.body.classList.add("page-exit");
          overlay.classList.add("visible");
        });

        // esperar un poco para simular redirección
        setTimeout(() => {
          // si el backend devuelve ruta de destino, usarla
          if (data.redirect) window.location.href = data.redirect;
          else window.location.href = "src/pages/home.php";
        }, 700);
      } else {
        // intentar extraer mensaje de error
        let msg = "Usuario o contraseña incorrectos";
        if (data.mensaje) msg = data.mensaje;
        if (data.error)
          msg =
            typeof data.error === "string" ?
              data.error
            : JSON.stringify(data.error);
        if (data.resultado) {
          try {
            const parsed =
              typeof data.resultado === "string" ?
                JSON.parse(data.resultado)
              : data.resultado;
            if (parsed.error) msg = parsed.error;
          } catch (e) {}
        }
        throw new Error(msg);
      }
    } catch (err) {
      card.classList.remove("loading");
      card.classList.add("error");
      showNotice("error", err.message || "Error de autenticación", 5000);
      setTimeout(() => card.classList.remove("error"), 600);
    } finally {
      submitBtn.disabled = false;
    }
  });

  // small UX: float labels for prefilled/autofill
  document.querySelectorAll(".field input").forEach((i) => {
    i.addEventListener("blur", () => {
      if (i.value) i.classList.add("filled");
      else i.classList.remove("filled");
    });
  });

  // Render verification UI when backend requests it
  function renderVerification(data, username) {
    const area = document.getElementById("verificationArea");
    if (!area) return;
    area.innerHTML = "";

    const wrapper = document.createElement("div");
    wrapper.className = "verification-card";

    const title = document.createElement("h3");
    title.textContent = "Verificación adicional requerida";
    wrapper.appendChild(title);

    const info = document.createElement("p");
    info.textContent =
      data.mensaje || "Completa el paso de verificación para continuar.";
    wrapper.appendChild(info);

    // If policy requires password change, show change-password form
    const requiresChange =
      data.politicas_seguridad &&
      data.politicas_seguridad.requiere_cambio_password;

    if (requiresChange) {
      const np1 = document.createElement("input");
      np1.type = "password";
      np1.placeholder = "Nueva contraseña";
      np1.id = "newPass1";
      np1.className = "field-input";

      const np2 = document.createElement("input");
      np2.type = "password";
      np2.placeholder = "Repite nueva contraseña";
      np2.id = "newPass2";
      np2.className = "field-input";

      const btnChange = document.createElement("button");
      btnChange.className = "btn primary";
      btnChange.textContent = "Cambiar contraseña";

      btnChange.addEventListener("click", async () => {
        const p1 = document.getElementById("newPass1").value;
        const p2 = document.getElementById("newPass2").value;
        if (!p1 || p1 !== p2) {
          showNotice("error", "Las contraseñas no coinciden");
          return;
        }
        btnChange.disabled = true;
        showNotice("info", "Enviando cambio de contraseña…", 0);
        try {
          const token = await fetchCsrfToken();
          const resp = await fetch(
            (BASE_URL || "..") +
              "/backend/public/index.php/api/login/change-password",
            {
              method: "POST",
              credentials: "include",
              headers: {
                "Content-Type": "application/json",
                ...(token ? { "X-CSRF-Token": token } : {}),
              },
              body: JSON.stringify({ username: username, new_password: p1 }),
            },
          );
          const resj = await resp.json();
          if (resp.ok && (resj.success || resj.message === "ok")) {
            showNotice(
              "success",
              "Contraseña actualizada. Inicia sesión.",
              4000,
            );
            area.innerHTML = "";
          } else {
            showNotice(
              "error",
              resj.mensaje || resj.error || "Error al cambiar contraseña",
              5000,
            );
          }
        } catch (e) {
          showNotice("error", e.message || "Error de red", 5000);
        } finally {
          btnChange.disabled = false;
        }
      });

      wrapper.appendChild(np1);
      wrapper.appendChild(np2);
      wrapper.appendChild(btnChange);
      area.appendChild(wrapper);
      return;
    }

    // Default: verification code input
    const codeInput = document.createElement("input");
    codeInput.type = "text";
    codeInput.placeholder = "Código de verificación";
    codeInput.id = "verificationCode";
    codeInput.className = "field-input";

    const btn = document.createElement("button");
    btn.className = "btn primary";
    btn.textContent = "Verificar";

    btn.addEventListener("click", async () => {
      const code = document.getElementById("verificationCode").value.trim();
      if (!code) {
        showNotice("error", "Ingresa el código de verificación");
        return;
      }
      btn.disabled = true;
      showNotice("info", "Verificando…", 0);
      try {
        const token = await fetchCsrfToken();
        const resp = await fetch(
          (BASE_URL || "..") + "/backend/public/index.php/api/login/verify",
          {
            method: "POST",
            credentials: "include",
            headers: {
              "Content-Type": "application/json",
              ...(token ? { "X-CSRF-Token": token } : {}),
            },
            body: JSON.stringify({ username: username, code }),
          },
        );
        const resj = await resp.json();
        if (resp.ok && (resj.success || resj.usuario)) {
          showNotice("success", "Verificación correcta. Redirigiendo...", 1200);
          setTimeout(() => (window.location.href = "src/pages/home.php"), 900);
        } else {
          showNotice(
            "error",
            resj.mensaje || resj.error || "Código inválido",
            5000,
          );
        }
      } catch (e) {
        showNotice("error", e.message || "Error de red", 5000);
      } finally {
        btn.disabled = false;
      }
    });

    wrapper.appendChild(codeInput);
    wrapper.appendChild(btn);
    area.appendChild(wrapper);
  }
});
