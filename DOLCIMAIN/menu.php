<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DOLCI Menu</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <div id="loader">
      <div class="cupcake">
        <div class="frosting"></div>
        <div class="sprinkles">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
        <div class="wrapper"></div>
      </div>
      <h2>Preparing something sweet...</h2>
    </div>

    <nav>
      <a href="index.php" class="logo">DOLCI</a>

      <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php" class="active">Menu</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="login.php" class="login-link">Login</a>
      </div>
    </nav>

    <svg
      class="icing-drip"
      viewBox="0 0 1440 40"
      preserveAspectRatio="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <path
        fill="var(--white)"
        d="M0,0 H1440 V10
      C1380,10 1380,34 1320,34
      C1260,34 1260,10 1200,10
      C1140,10 1140,34 1080,34
      C1020,34 1020,10 960,10
      C900,10 900,34 840,34
      C780,34 780,10 720,10
      C660,10 660,34 600,34
      C540,34 540,10 480,10
      C420,10 420,34 360,34
      C300,34 300,10 240,10
      C180,10 180,34 120,34
      C60,34 60,10 0,10 Z"
      />
    </svg>

    <main class="page-container">
      <h1 class="page-title">Choose among our dedicated selections!</h1>
      <p class="page-subtitle">
        Pick a flavor, build it up in tiers, and customize every layer.
      </p>

      <!-- Flavor Showcase -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Choose Your Flavor</h2>
        </div>

        <div class="flavor-grid" id="flavorGrid">
          <div class="flavor-card" data-cakeid="1" data-flavor="vanilla" data-price="500">
            <div class="flavor-cake flavor-vanilla">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Vanilla</h3>
            <p class="price">₱500 base</p>
          </div>

          <div class="flavor-card" data-cakeid="2" data-flavor="Chocolate" data-price="550">
            <div class="flavor-cake flavor-chocolate">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Chocolate</h3>
            <p class="price">₱550 base</p>
          </div>

          <div class="flavor-card" data-cakeid="3" data-flavor="strawberry" data-price="600">
            <div class="flavor-cake flavor-strawberry">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Strawberry</h3>
            <p class="price">₱600 base</p>
          </div>

          <div class="flavor-card" data-cakeid="4" data-flavor="redvelvet" data-price="650">
            <div class="flavor-cake flavor-redvelvet">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Red Velvet</h3>
            <p class="price">₱650 base</p>
          </div>

          <div class="flavor-card" data-cakeid="5" data-flavor="mango" data-price="600">
            <div class="flavor-cake flavor-mango">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Mango</h3>
            <p class="price">₱600 base</p>
          </div>
        </div>
      </section>

      <br />

      <!-- Tiers -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">How Many Tiers?</h2>
          <span class="price-tag" id="tierPriceTag">+₱0</span>
        </div>
        <p class="page-subtitle" style="margin-bottom: 20px">
          Every tier past the first adds ₱300.
        </p>

        <div class="tier-stepper">
          <button type="button" id="tierMinus" aria-label="Remove a tier">
            −
          </button>
          <span class="tier-count" id="tierCount">1</span>
          <button type="button" id="tierPlus" aria-label="Add a tier">+</button>
        </div>
      </section>

      <br />

      <!-- Icing & Filling -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Icing &amp; Filling</h2>
        </div>

        <div class="mode-toggle" id="modeToggle">
          <button type="button" class="active" data-mode="uniform">
            Same for all tiers
          </button>
          <button type="button" data-mode="perlayer">Different per tier</button>
        </div>

        <br />

        <!-- Uniform mode -->
        <div class="customization-grid" id="uniformLayer">
          <div class="form-group">
            <label>Icing</label>
            <select id="uniformIcing">
              <option>Buttercream</option>
              <option>Whipped Cream</option>
              <option>Chocolate Ganache</option>
              <option>Cream Cheese Frosting</option>
              <option>Fondant</option>
            </select>
          </div>

          <div class="form-group">
            <label>Filling</label>
            <select id="uniformFilling">
              <option>Chocolate Mousse</option>
              <option>Fresh Strawberries</option>
              <option>Vanilla Cream</option>
              <option>Cookies &amp; Cream</option>
              <option>Salted Caramel</option>
              <option>Blueberry Jam</option>
              <option>No Filling</option>
            </select>
          </div>
        </div>

        <!-- Per-layer mode -->
        <div id="perLayerContainer" style="display: none"></div>

        <div class="form-group" style="margin-top: 10px">
          <label>Dedication Message</label>
          <input
            type="text"
            id="dedicationMessage"
            maxlength="40"
            placeholder="Happy Birthday Anna!"
          />
        </div>
      </section>

      <br />

      <!-- Decorations -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Extra Decorations</h2>
        </div>

        <div class="checkbox-grid" id="decorationsGrid">
          <label>
            <input type="checkbox" data-price="50" />
            <span>Fresh Fruits <em>+₱50</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="30" />
            <span>Sprinkles <em>+₱30</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="70" />
            <span>Chocolate Drip <em>+₱70</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="120" />
            <span>Macarons <em>+₱120</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="150" />
            <span>Gold Leaf <em>+₱150</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="100" />
            <span>Edible Flowers <em>+₱100</em></span>
          </label>
        </div>
      </section>

      <br />

      <!-- Price Summary -->
      <section class="card price-summary">
        <h2 class="section-title">Price Summary</h2>

        <div class="price-line">
          <span id="summaryFlavorLabel">Vanilla base</span>
          <span class="amount" id="summaryFlavorPrice">₱500</span>
        </div>
        <div class="price-line">
          <span id="summaryTierLabel">1 tier</span>
          <span class="amount" id="summaryTierPrice">+₱0</span>
        </div>
        <div class="price-line">
          <span>Decorations</span>
          <span class="amount" id="summaryDecorPrice">+₱0</span>
        </div>
        <div class="price-line price-total">
          <span>Total</span>
          <span class="amount" id="summaryTotalPrice">₱500</span>
        </div>
      </section>

      <br />

      <div style="text-align: center">
        <button id="addToCartBtn" type="button" class="btn btn-primary">
          Add to Cart
        </button>
      </div>
    </main>

    <footer>
      <p>&copy; 2026 DOLCI</p>
    </footer>
    <script src="js/loader.js"></script>
    <script src="js/menu.js"></script>
    <script>
      const TIER_PRICE = 300;
      const MAX_TIERS = 4;

      const flavorCards = document.querySelectorAll(".flavor-card");
      const tierMinus = document.getElementById("tierMinus");
      const tierPlus = document.getElementById("tierPlus");
      const tierCountEl = document.getElementById("tierCount");
      const tierPriceTag = document.getElementById("tierPriceTag");
      const modeToggle = document.getElementById("modeToggle");
      const uniformLayer = document.getElementById("uniformLayer");
      const perLayerContainer = document.getElementById("perLayerContainer");
      const decorationCheckboxes = document.querySelectorAll(
        "#decorationsGrid input[type='checkbox']",
      );

      const summaryFlavorLabel = document.getElementById("summaryFlavorLabel");
      const summaryFlavorPrice = document.getElementById("summaryFlavorPrice");
      const summaryTierLabel = document.getElementById("summaryTierLabel");
      const summaryTierPrice = document.getElementById("summaryTierPrice");
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
        tiers: 1,
        mode: "uniform",
      };

      function buildSelect(options) {
        return options.map((o) => `<option>${o}</option>`).join("");
      }

      function renderPerLayerRows() {
        perLayerContainer.innerHTML = "";
        for (let i = 1; i <= state.tiers; i++) {
          const row = document.createElement("div");
          row.className = "layer-row";
          row.innerHTML = `
            <span class="layer-label">Tier ${i}</span>
            <div class="form-group">
              <label>Icing</label>
              <select>${buildSelect(icingOptions)}</select>
            </div>
            <div class="form-group">
              <label>Filling</label>
              <select>${buildSelect(fillingOptions)}</select>
            </div>
          `;
          perLayerContainer.appendChild(row);
        }
      }

      function updateModeVisibility() {
        if (state.mode === "uniform") {
          uniformLayer.style.display = "grid";
          perLayerContainer.style.display = "none";
        } else {
          uniformLayer.style.display = "none";
          perLayerContainer.style.display = "block";
          renderPerLayerRows();
        }
      }

      function updateSummary() {
        const decorTotal = Array.from(decorationCheckboxes)
          .filter((c) => c.checked)
          .reduce((sum, c) => sum + Number(c.dataset.price), 0);

        const tierAddOn = (state.tiers - 1) * TIER_PRICE;
        const total = state.flavorPrice + tierAddOn + decorTotal;

        summaryFlavorLabel.textContent = `${state.flavorName} base`;
        summaryFlavorPrice.textContent = `₱${state.flavorPrice}`;
        summaryTierLabel.textContent = `${state.tiers} tier${state.tiers > 1 ? "s" : ""}`;
        summaryTierPrice.textContent = `+₱${tierAddOn}`;
        summaryDecorPrice.textContent = `+₱${decorTotal}`;
        summaryTotalPrice.textContent = `₱${total}`;
        tierPriceTag.textContent = `+₱${tierAddOn}`;
      }

      // Flavor selection
      flavorCards.forEach((card) => {
        card.addEventListener("click", () => {
          flavorCards.forEach((c) => c.classList.remove("selected"));
          card.classList.add("selected");
          state.flavorName = card.querySelector("h3").textContent.trim();
          state.flavorPrice = Number(card.dataset.price);
          updateSummary();
        });
      });
      flavorCards[0].classList.add("selected");

      // Tier stepper
      function setTiers(n) {
        state.tiers = Math.min(MAX_TIERS, Math.max(1, n));
        tierCountEl.textContent = state.tiers;
        tierMinus.disabled = state.tiers <= 1;
        tierPlus.disabled = state.tiers >= MAX_TIERS;
        if (state.mode === "perlayer") renderPerLayerRows();
        updateSummary();
      }
      tierMinus.addEventListener("click", () => setTiers(state.tiers - 1));
      tierPlus.addEventListener("click", () => setTiers(state.tiers + 1));

      // Mode toggle
      modeToggle.querySelectorAll("button").forEach((btn) => {
        btn.addEventListener("click", () => {
          modeToggle
            .querySelectorAll("button")
            .forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
          state.mode = btn.dataset.mode;
          updateModeVisibility();
        });
      });

      // Decorations
      decorationCheckboxes.forEach((c) =>
        c.addEventListener("change", updateSummary),
      );

      // Init
      setTiers(1);
      updateModeVisibility();
      updateSummary();

      document
        .getElementById("addToCartBtn")
        .addEventListener("click", addToCart);

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
    </script>
  </body>
</html>
