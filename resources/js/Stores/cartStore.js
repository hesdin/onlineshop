import { computed, reactive } from 'vue';

const createDefaultState = () => ({
  items: [],
  items_count: 0,
  total: 0,
  updated_at: 0,
});

const state = reactive(createDefaultState());

const normalizeNumber = (value) => {
  const numeric = Number.parseInt(value ?? 0, 10);
  return Number.isNaN(numeric) ? 0 : numeric;
};

const cloneItems = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }
  return items.map((item) => ({ ...item }));
};

const normalizeCartPayload = (cart) => {
  const updatedAt = cart?.updated_at != null ? Number(cart.updated_at) : Date.now();

  return {
    items: cloneItems(cart?.items),
    items_count: normalizeNumber(cart?.items_count),
    total: normalizeNumber(cart?.total),
    updated_at: Number.isNaN(updatedAt) ? Date.now() : updatedAt,
  };
};

const applyState = (next) => {
  state.items = next.items;
  state.items_count = next.items_count;
  state.total = next.total;
  state.updated_at = next.updated_at;
};

const syncCartStore = (cart, { force = false } = {}) => {
  const normalized = cart ? normalizeCartPayload(cart) : { ...createDefaultState(), updated_at: Date.now() };
  if (!force && normalized.updated_at < state.updated_at) {
    return;
  }
  applyState(normalized);
};

const resetCartStore = () => {
  applyState({ ...createDefaultState(), updated_at: Date.now() });
};

const useCartStore = () => {
  const items = computed(() => state.items ?? []);
  const count = computed(() => state.items_count ?? 0);
  const total = computed(() => state.total ?? 0);
  const updatedAt = computed(() => state.updated_at ?? 0);

  return {
    state,
    items,
    count,
    total,
    updatedAt,
  };
};

export { resetCartStore, syncCartStore, useCartStore };
