document.getElementById("addToCartBtn").addEventListener("click", addToCart);

async function addToCart() {
  const decorations = [];

  decorationCheckboxes.forEach((box) => {
    if (box.checked) {
      decorations.push(
        box.parentElement.innerText.replace(/\+\₱.*/, "").trim(),
      );
    }
  });

  let icing;
  let filling;

  if (state.mode === "uniform") {
    icing = document.getElementById("uniformIcing").value;

    filling = document.getElementById("uniformFilling").value;
  } else {
    icing = [];
    filling = [];

    document
      .querySelectorAll("#perLayerContainer .layer-row")
      .forEach((row, index) => {
        const selects = row.querySelectorAll("select");

        icing.push({
          tier: index + 1,
          value: selects[0].value,
        });

        filling.push({
          tier: index + 1,
          value: selects[1].value,
        });
      });
  }

  const cake = {
    flavor: state.flavorName,

    base_price: state.flavorPrice,

    tiers: state.tiers,

    icing: icing,

    filling: filling,

    decorations: decorations,

    dedication: document.getElementById("dedicationMessage").value,

    total: Number(summaryTotalPrice.textContent.replace(/[₱,]/g, "")),
  };

  try {
    const response = await fetch("backend/add_to_cart.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(cake),
    });

    const result = await response.json();

    if (result.success) {
      alert("🍰 Cake added to cart!");

      window.location = "cart.php";
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error(error);

    alert("Unable to add cake to cart.");
  }
}
