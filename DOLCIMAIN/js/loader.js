function hideLoader() {
  const loader = document.getElementById("loader");
  if (loader) {
    loader.classList.add("hidden");
  }
}

if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  window.setTimeout(hideLoader, 200);
} else {
  window.addEventListener("load", hideLoader, { once: true });
  document.addEventListener(
    "DOMContentLoaded",
    () => {
      window.setTimeout(hideLoader, 200);
    },
    { once: true },
  );
}
