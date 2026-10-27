import '@testing-library/jest-dom/vitest';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';
window.scrollTo = vi.fn();
window.matchMedia = vi.fn((query) => ({ matches: false, media: query, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
HTMLDialogElement.prototype.showModal = function () { this.open = true; };
HTMLDialogElement.prototype.close = function () { this.open = false; this.dispatchEvent(new Event('close')); };
afterEach(() => { cleanup(); vi.restoreAllMocks(); });
