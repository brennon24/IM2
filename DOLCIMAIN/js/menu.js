const TIER_PRICE = 300;
const MAX_TIERS = 4;

const flavorCards = Array.from(document.querySelectorAll(".flavor-card"));
const tierMinus = document.getElementById("tierMinus");
const tierPlus = document.getElementById("tierPlus");
const tierCountEl = document.getElementById("tierCount");
const tierPriceTag = document.getElementById("tierPriceTag");
const modeToggle = document.getElementById("modeToggle");
const uniformLayer = document.getElementById("uniformLayer");
const perLayerContainer = document.getElementById("perLayerContainer");
const decorationCheckboxes = Array.from(
  document.querySelectorAll("#decorationsGrid input[type='checkbox']"),
);
const dedicationInput = document.getElementById("dedicationMessage");
const addToCartBtn = document.getElementById("addToCartBtn");

const summaryFlavorLabel = document.getElementById("summaryFlavorLabel");
const summaryFlavorPrice = document.getElementById("summaryFlavorPrice");
const summaryTierLabel = document.getElementById("summaryTierLabel");
const summaryTierPrice = document.getElementById("summaryTierPrice");
const summaryCustomizationPrice = document.getElementById(
  "summaryCustomizationPrice",
);
const summaryDecorPrice = document.getElementById("summaryDecorPrice");
const summaryTotalPrice = document.getElementById("summaryTotalPrice");

const icingOptions = [
  "Buttercream",
  "Whipped Cream",
  "Chocolate Ganache",
  "Cream Cheese Frosting",
  "Fondant",
];
const fillingOptions = [
  "Chocolate Mousse",
  "Fresh Strawberries",
  "Vanilla Cream",
  "Cookies & Cream",
  "Salted Caramel",
  "Blueberry Jam",
  "No Filling",
];

let state = {
  flavorName: "Vanilla",
  flavorPrice: 500,
  cakeId: 1,
  tiers: 1,
  mode: "uniform",
};

function buildSelect(options, selectedValue) {
  return options
    .map(
      (option) =>
        `<option value="${option}" ${option === selectedValue ? "selected" : ""}>${option}</option>`,
    )
    .join("");
}

function renderPerLayerRows(prefillIcing, prefillFilling) {
  if (!perLayerContainer) return;
  perLayerContainer.innerHTML = "";
  for (let i = 1; i <= state.tiers; i++) {
    const row = document.createElement("div");
    row.className = "layer-row";

    const icingValue =
      prefillIcing && Array.isArray(prefillIcing)
        ? prefillIcing.find((entry) => Number(entry.tier) === i)?.value
        : undefined;
    const fillingValue =
      prefillFilling && Array.isArray(prefillFilling)
        ? prefillFilling.find((entry) => Number(entry.tier) === i)?.value
        : undefined;

    row.innerHTML = `
      <span class="layer-label">Tier ${i}</span>
      <div class="form-group">
        <label>Icing</label>
        <select>${buildSelect(icingOptions, icingValue)}</select>
      </div>
      <div class="form-group">
        <label>Filling</label>
        <select>${buildSelect(fillingOptions, fillingValue)}</select>
      </div>
    `;
    row
      .querySelectorAll("select")
      .forEach((select) => select.addEventListener("change", updateSummary));
    perLayerContainer.appendChild(row);
  }
}

function updateModeVisibility(prefillIcing, prefillFilling) {
  if (!uniformLayer || !perLayerContainer) return;
  if (state.mode === "uniform") {
    uniformLayer.style.display = "grid";
    perLayerContainer.style.display = "none";
  } else {
    uniformLayer.style.display = "none";
    perLayerContainer.style.display = "block";
    renderPerLayerRows(prefillIcing, prefillFilling);
  }
}

function updateSummary() {
  const decorTotal = decorationCheckboxes
    .filter((checkbox) => checkbox.checked)
    .reduce((sum, checkbox) => sum + Number(checkbox.dataset.price || 0), 0);
  const tierAddOn = (state.tiers - 1) * TIER_PRICE;
  const customizationCost = 0;
  const total = state.flavorPrice + tierAddOn + customizationCost + decorTotal;

  if (summaryFlavorLabel)
    summaryFlavorLabel.textContent = `${state.flavorName} base`;
  if (summaryFlavorPrice)
    summaryFlavorPrice.textContent = `₱${state.flavorPrice}`;
  if (summaryTierLabel)
    summaryTierLabel.textContent = `${state.tiers} tier${state.tiers > 1 ? "s" : ""}`;
  if (summaryTierPrice) summaryTierPrice.textContent = `+₱${tierAddOn}`;
  if (summaryCustomizationPrice)
    summaryCustomizationPrice.textContent = `+₱${customizationCost}`;
  if (summaryDecorPrice) summaryDecorPrice.textContent = `+₱${decorTotal}`;
  if (summaryTotalPrice) summaryTotalPrice.textContent = `₱${total}`;
  if (tierPriceTag) tierPriceTag.textContent = `+₱${tierAddOn}`;
}

function selectFlavorCard(card) {
  flavorCards.forEach((item) => {
    item.classList.remove("selected");
    item.setAttribute("aria-pressed", "false");
  });

  card.classList.add("selected");
  card.setAttribute("aria-pressed", "true");
  state.flavorName = card.querySelector("h3")?.textContent?.trim() || "Custom";
  state.flavorPrice = Number(card.dataset.price || 0);
  state.cakeId = Number(card.dataset.cakeid || 0);
  updateSummary();
}

function setTiers(count, prefillIcing, prefillFilling) {
  state.tiers = Math.min(MAX_TIERS, Math.max(1, Number(count) || 1));
  if (tierCountEl) tierCountEl.textContent = state.tiers;
  if (tierMinus) tierMinus.disabled = state.tiers <= 1;
  if (tierPlus) tierPlus.disabled = state.tiers >= MAX_TIERS;
  if (state.mode === "perlayer") {
    renderPerLayerRows(prefillIcing, prefillFilling);
  }
  updateSummary();
}

function attachEvents() {
  flavorCards.forEach((card) => {
    card.addEventListener("click", () => selectFlavorCard(card));
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        selectFlavorCard(card);
      }
    });
    card.setAttribute("tabindex", "0");
    card.setAttribute("role", "button");
    card.setAttribute("aria-pressed", "false");
  });

  if (tierMinus)
    tierMinus.addEventListener("click", () => setTiers(state.tiers - 1));
  if (tierPlus)
    tierPlus.addEventListener("click", () => setTiers(state.tiers + 1));

  modeToggle?.querySelectorAll("button").forEach((button) => {
    button.addEventListener("click", () => {
      modeToggle
        .querySelectorAll("button")
        .forEach((item) => item.classList.remove("active"));
      button.classList.add("active");
      state.mode = button.dataset.mode || "uniform";
      updateModeVisibility();
      updateSummary();
    });
  });

  document
    .getElementById("uniformIcing")
    ?.addEventListener("change", updateSummary);
  document
    .getElementById("uniformFilling")
    ?.addEventListener("change", updateSummary);
  decorationCheckboxes.forEach((checkbox) =>
    checkbox.addEventListener("change", updateSummary),
  );
}

function applyEditItem(item) {
  const matchingFlavorCard = flavorCards.find(
    (card) => card.querySelector("h3")?.textContent?.trim() === item.flavor,
  );
  flavorCards.forEach((card) => card.classList.remove("selected"));
  if (matchingFlavorCard) {
    matchingFlavorCard.classList.add("selected");
    state.flavorName = item.flavor;
    state.flavorPrice = Number(
      item.base_price ?? matchingFlavorCard.dataset.price ?? 0,
    );
    state.cakeId = Number(
      item.cakeId ?? matchingFlavorCard.dataset.cakeid ?? 0,
    );
  }

  const isPerLayer = Array.isArray(item.icing);
  state.mode = isPerLayer ? "perlayer" : "uniform";
  modeToggle?.querySelectorAll("button").forEach((button) => {
    button.classList.toggle("active", button.dataset.mode === state.mode);
  });

  if (document.getElementById("uniformIcing") && !isPerLayer) {
    document.getElementById("uniformIcing").value = item.icing || "Buttercream";
  }
  if (document.getElementById("uniformFilling") && !isPerLayer) {
    document.getElementById("uniformFilling").value =
      item.filling || "Chocolate Mousse";
  }

  setTiers(
    Number(item.tiers) || 1,
    isPerLayer ? item.icing : null,
    isPerLayer ? item.filling : null,
  );
  updateModeVisibility(
    isPerLayer ? item.icing : null,
    isPerLayer ? item.filling : null,
  );

  decorationCheckboxes.forEach((box) => {
    const label =
      box.parentElement?.textContent?.replace(/\+₱.*/, "").trim() || "";
    box.checked =
      Array.isArray(item.decorations) && item.decorations.includes(label);
  });

  if (dedicationInput) dedicationInput.value = item.dedication || "";
  updateSummary();
}

function init() {
  attachEvents();
  if (typeof editItem !== "undefined" && editItem) {
    applyEditItem(editItem);
  } else {
    flavorCards.forEach((card) => card.classList.remove("selected"));
    flavorCards[0]?.classList.add("selected");
    flavorCards[0]?.setAttribute("aria-pressed", "true");
    state.flavorName =
      flavorCards[0]?.querySelector("h3")?.textContent?.trim() || "Vanilla";
    state.flavorPrice = Number(flavorCards[0]?.dataset.price || 500);
    state.cakeId = Number(flavorCards[0]?.dataset.cakeid || 0);
    setTiers(1);
    updateModeVisibility();
    updateSummary();
  }
}

init();

addToCartBtn?.addEventListener("click", async () => {
  const decorations = decorationCheckboxes
    .filter((box) => box.checked)
    .map((box) => box.parentElement?.textContent?.replace(/\+₱.*/, "").trim())
    .filter(Boolean);

  let icing;
  let filling;

  if (state.mode === "uniform") {
    icing = document.getElementById("uniformIcing")?.value || "Buttercream";
    filling =
      document.getElementById("uniformFilling")?.value || "Chocolate Mousse";
  } else {
    icing = [];
    filling = [];
    document
      .querySelectorAll("#perLayerContainer .layer-row")
      .forEach((row, index) => {
        const selects = row.querySelectorAll("select");
        icing.push({
          tier: index + 1,
          value: selects[0]?.value || "Buttercream",
        });
        filling.push({
          tier: index + 1,
          value: selects[1]?.value || "Chocolate Mousse",
        });
      });
  }

  const cake = {
    flavor: state.flavorName,
    base_price: state.flavorPrice,
    cakeId: state.cakeId,
    tiers: state.tiers,
    icing,
    filling,
    decorations,
    dedication: dedicationInput?.value || "",
    total: Number((summaryTotalPrice?.textContent || "0").replace(/[₱,]/g, "")),
  };

  const isEditing = typeof editItem !== "undefined" && editItem;
  const endpoint = isEditing
    ? "backend/update_cart.php"
    : "backend/add_to_cart.php";
  if (isEditing) cake.id = editItem.id;

  try {
    const response = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(cake),
    });

    const result = await response.json();
    if (result.success) {
      alert(isEditing ? "🍰 Cake updated!" : "🍰 Cake added to cart!");
      window.location.href = "cart.php";
    } else {
      alert(result.message || "Unable to save this cake.");
      if (result.redirect) {
        window.location = result.redirect;
      }
    }
  } catch (error) {
    console.error(error);
    alert("Unable to save this cake.");
  }
});
