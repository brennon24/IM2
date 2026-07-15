console.log("loader.js loaded");

window.addEventListener("load", () => {
  console.log("Window loaded");

  const loader = document.getElementById("loader");

  setTimeout(() => {
    console.log("Hiding loader");

    loader.classList.add("hidden");
  }, 800);
});
