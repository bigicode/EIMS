const memoryStore = {};

const hasLocalStorage = () => {
  try {
    return typeof window !== 'undefined' && !!window.localStorage;
  } catch (error) {
    return false;
  }
};

const parseJson = (value, fallback) => {
  if (value === null || value === undefined) {
    return fallback;
  }

  try {
    return JSON.parse(value);
  } catch (error) {
    return fallback;
  }
};

export const storageService = {
  get(key, fallback) {
    if (hasLocalStorage()) {
      return parseJson(window.localStorage.getItem(key), fallback);
    }

    return key in memoryStore ? memoryStore[key] : fallback;
  },

  set(key, value) {
    if (hasLocalStorage()) {
      window.localStorage.setItem(key, JSON.stringify(value));
      return;
    }

    memoryStore[key] = value;
  },

  remove(key) {
    if (hasLocalStorage()) {
      window.localStorage.removeItem(key);
      return;
    }

    delete memoryStore[key];
  },
};
