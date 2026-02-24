const btn = document.getElementById("btn");
const out = document.getElementById("output");
btn.addEventListener("click", async () => {
  out.textContent = "Cargando...";
  try {
    const res = await fetch("../backend/public/index.php/api/hello");
    const data = await res.json();
    out.textContent = JSON.stringify(data, null, 2);
  } catch (err) {
    out.textContent = "Error: " + err.message;
  }
});
