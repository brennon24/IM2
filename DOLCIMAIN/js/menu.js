const TIER_PRICE = 300;
const MAX_TIERS = 4;
const PX_PER_INCH = 16; // visualization scale
const TIER_HEIGHT_IN = 2.5; // assumed height per tier, inches
const PLATE_DIAMETER_IN = 10; // standard dinner plate, for comparison
const TIER_TAPER = 0.14; // each tier up shrinks by this fraction

const servingEstimates = {
  6: 7,
  8: 11,
  10: 17.5,
  12: 27,
};

const flavorCards = Array.from(document.querySelectorAll(".flavor-card"));
const sizeCards = Array.from(document.querySelectorAll(".size-card"));
const tierMinus = document.getElementById("tierMinus");
const tierPlus = document.getElementById("tierPlus");
const tierCountEl = document.getElementById("tierCount");
const tierPriceTag = document.getElementById("tierPriceTag");
const sizePriceTag = document.getElementById("sizePriceTag");
const modeToggle = document.getElementById("modeToggle");
const uniformLayer = document.getElementById("uniformLayer");
const perLayerContainer = document.getElementById("perLayerContainer");
const decorationCheckboxes = Array.from(
  document.querySelectorAll("#decorationsGrid input[type='checkbox']"),
);
const dedicationInput = document.getElementById("dedicationMessage");
const addToCartBtn = document.getElementById("addToCartBtn");

const visualPlate = document.getElementById("visualPlate");
const visualCakeStack = document.getElementById("visualCakeStack");
const visualRuler = document.getElementById("visualRuler");
const visualReadout = document.getElementById("visualReadout");

const summaryFlavorLabel = document.getElementById("summaryFlavorLabel");
const summaryFlavorPrice = document.getElementById("summaryFlavorPrice");
const summarySizeLabel = document.getElementById("summarySizeLabel");
const summarySizePrice = document.getElementById("summarySizePrice");
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
  flavorId: "vanilla",
  cakeId: 1,
  sizeDiameter: 8,
  sizePrice: 0,
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

// ---------- Visualization ----------

function renderVisualization() {
  if (!visualCakeStack || !visualPlate || !visualRuler || !visualReadout)
    return;

  const baseWidthPx = state.sizeDiameter * PX_PER_INCH;
  const tierHeightPx = TIER_HEIGHT_IN * PX_PER_INCH;
  const plateWidthPx = PLATE_DIAMETER_IN * PX_PER_INCH;

  // Plate (fixed reference size)
  visualPlate.style.width = plateWidthPx + "px";

  // Build cake tiers, bottom (widest) to top (narrowest)
  visualCakeStack.innerHTML = "";
  const totalHeightPx = tierHeightPx * state.tiers;
  visualCakeStack.style.height = totalHeightPx + "px";
  visualCakeStack.style.width = baseWidthPx + "px";

  for (let i = 0; i < state.tiers; i++) {
    const shrink = 1 - TIER_TAPER * i;
    const tierWidthPx = Math.max(baseWidthPx * shrink, baseWidthPx * 0.55);

    const tierWrap = document.createElement("div");
    tierWrap.className = `tier-wrap flavor-${state.flavorId}`;
    tierWrap.style.width = tierWidthPx + "px";

    const drip = document.createElement("div");
    drip.className = "flavor-cake-drip tier-drip";
    drip.style.height = Math.round(tierHeightPx * 0.32) + "px";

    const body = document.createElement("div");
    body.className = "flavor-cake-body tier-body";
    body.style.height = Math.round(tierHeightPx * 0.68) + "px";

    tierWrap.appendChild(drip);
    tierWrap.appendChild(body);
    // Insert so the bottom tier ends up at the bottom of the stack visually
    visualCakeStack.insertBefore(tierWrap, visualCakeStack.firstChild);
  }

  // Ruler — ticks every inch from 0 to 14, labeled every 2
  const rulerMaxInches = 14;
  visualRuler.innerHTML = "";
  visualRuler.style.width = rulerMaxInches * PX_PER_INCH + "px";
  for (let inch = 0; inch <= rulerMaxInches; inch++) {
    const tick = document.createElement("div");
    tick.className = "ruler-tick" + (inch % 2 === 0 ? " major" : "");
    tick.style.left = inch * PX_PER_INCH + "px";
    if (inch % 2 === 0) {
      const label = document.createElement("span");
      label.className = "ruler-label";
      label.textContent = inch + '"';
      tick.appendChild(label);
    }
    visualRuler.appendChild(tick);
  }

  // Readout text
  const totalHeightIn = (TIER_HEIGHT_IN * state.tiers).toFixed(1);
  const diff = state.sizeDiameter - PLATE_DIAMETER_IN;
  let compareText;
  if (diff === 0) {
    compareText = `Exactly the width of a standard ${PLATE_DIAMETER_IN}-inch dinner plate.`;
  } else if (diff > 0) {
    compareText = `${diff}" wider than a standard ${PLATE_DIAMETER_IN}-inch dinner plate.`;
  } else {
    compareText = `${Math.abs(diff)}" narrower than a standard ${PLATE_DIAMETER_IN}-inch dinner plate.`;
  }

  const perTierServing = servingEstimates[state.sizeDiameter] || 11;
  const totalServings = Math.round(perTierServing * state.tiers);

  visualReadout.innerHTML = `
    <div class="readout-line"><strong>${state.sizeDiameter}" diameter</strong> · ${compareText}</div>
    <div class="readout-line"><strong>${totalHeightIn}" tall</strong> across ${state.tiers} tier${state.tiers > 1 ? "s" : ""}</div>
    <div class="readout-line">Feeds approximately <strong>${totalServings} people</strong></div>
  `;
}

function updateSummary() {
  const decorTotal = decorationCheckboxes
    .filter((checkbox) => checkbox.checked)
    .reduce((sum, checkbox) => sum + Number(checkbox.dataset.price || 0), 0);
  const tierAddOn = (state.tiers - 1) * TIER_PRICE;
  const customizationCost = 0;
  const total =
    state.flavorPrice +
    state.sizePrice +
    tierAddOn +
    customizationCost +
    decorTotal;

  if (summaryFlavorLabel)
    summaryFlavorLabel.textContent = `${state.flavorName} base`;
  if (summaryFlavorPrice)
    summaryFlavorPrice.textContent = `₱${state.flavorPrice}`;
  if (summarySizeLabel)
    summarySizeLabel.textContent = `${state.sizeDiameter}" size`;
  if (summarySizePrice)
    summarySizePrice.textContent =
      state.sizePrice >= 0
        ? `+₱${state.sizePrice}`
        : `-₱${Math.abs(state.sizePrice)}`;
  if (summaryTierLabel)
    summaryTierLabel.textContent = `${state.tiers} tier${state.tiers > 1 ? "s" : ""}`;
  if (summaryTierPrice) summaryTierPrice.textContent = `+₱${tierAddOn}`;
  if (summaryCustomizationPrice)
    summaryCustomizationPrice.textContent = `+₱${customizationCost}`;
  if (summaryDecorPrice) summaryDecorPrice.textContent = `+₱${decorTotal}`;
  if (summaryTotalPrice) summaryTotalPrice.textContent = `₱${total}`;
  if (tierPriceTag) tierPriceTag.textContent = `+₱${tierAddOn}`;
  if (sizePriceTag)
    sizePriceTag.textContent =
      state.sizePrice >= 0
        ? `+₱${state.sizePrice}`
        : `-₱${Math.abs(state.sizePrice)}`;

  renderVisualization();
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
  state.flavorId = card.dataset.flavor
    ? card.dataset.flavor.toLowerCase().replace(/\s+/g, "")
    : "vanilla";
  updateSummary();
}

function selectSizeCard(card) {
  sizeCards.forEach((item) => item.classList.remove("selected"));
  card.classList.add("selected");
  state.sizeDiameter = Number(card.dataset.diameter || 8);
  state.sizePrice = Number(card.dataset.price || 0);
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

  sizeCards.forEach((card) => {
    card.addEventListener("click", () => selectSizeCard(card));
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
    state.flavorId = matchingFlavorCard.dataset.flavor
      ? matchingFlavorCard.dataset.flavor.toLowerCase().replace(/\s+/g, "")
      : "vanilla";
  }

  // Size — parse the leading number out of a saved string like "10 inch"
  const savedDiameter = parseInt(item.size, 10) || 8;
  const matchingSizeCard = sizeCards.find(
    (card) => Number(card.dataset.diameter) === savedDiameter,
  );
  sizeCards.forEach((card) => card.classList.remove("selected"));
  if (matchingSizeCard) {
    matchingSizeCard.classList.add("selected");
    state.sizeDiameter = savedDiameter;
    state.sizePrice = Number(matchingSizeCard.dataset.price || 0);
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
    state.flavorId = flavorCards[0]?.dataset.flavor
      ? flavorCards[0].dataset.flavor.toLowerCase().replace(/\s+/g, "")
      : "vanilla";

    // Default size card is already marked class="selected" in the HTML (8")
    const defaultSizeCard =
      sizeCards.find((card) => card.classList.contains("selected")) ||
      sizeCards[0];
    if (defaultSizeCard) {
      state.sizeDiameter = Number(defaultSizeCard.dataset.diameter || 8);
      state.sizePrice = Number(defaultSizeCard.dataset.price || 0);
    }

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
    size: `${state.sizeDiameter} inch`,
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
