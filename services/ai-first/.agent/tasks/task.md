# Lead Grid Restoration V2 (Clean Branch)

This task list tracks the restoration of the Seller Workspace and interactive Lead Grid, ensuring a clean implementation on the `feature/leadgrid-details-v2` branch.

- [ ] **Backend Implementation (Renaming)** <!-- id: 0 -->
    - [x] Create `seller_workspace` module structure (Client User) <!-- id: 1 -->
    - [x] Create `manager_workspace` module structure (Client Admin) <!-- id: 14 -->
    - [x] Implement `seller_workspace/router.py` & `schema.py` (The Grid) <!-- id: 2 -->
    - [x] Register NEW routers in `main.py` <!-- id: 4 -->
- [ ] **Frontend Implementation** <!-- id: 5 -->
    - [x] Implement `CustomLeadsGrid.js` (Engine) <!-- id: 6 -->
    - [x] Ensure `GridFilters.js` is present and integrated <!-- id: 7 -->
    - [x] Update `registry.js` to map `custom-leads-grid` <!-- id: 8 -->
    - [x] Update `hydration.js` to instantiate `CustomLeadsGrid` <!-- id: 9 -->
- [ ] **Verification** <!-- id: 10 -->
    - [x] Cache bust `index.html` (New Version) <!-- id: 11 -->
    - [x] Verify Dashboard loads with Grid (not Cards) <!-- id: 12 -->
    - [x] Verify Filters, Sorting, and Icons (Styles Restored) <!-- id: 13 -->
