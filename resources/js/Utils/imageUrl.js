// Resolve a product image reference into a usable URL.
// Handles: absolute URLs, storage paths, and bare filenames. Falls back to a placeholder.

const PLACEHOLDER =
    'data:image/svg+xml;utf8,' +
    encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="#eef2ff"/><text x="50%" y="50%" font-size="48" text-anchor="middle" dominant-baseline="central">📦</text></svg>'
    );

/**
 * @param {string|null|undefined} image - URL, "/storage/..", "products/x.jpg", or filename
 * @returns {string} usable <img src> value
 */
export function getProductImageUrl(image) {
    if (!image || typeof image !== 'string') return PLACEHOLDER;

    const val = image.trim();
    if (!val) return PLACEHOLDER;

    // Already absolute or data/blob URL
    if (/^(https?:)?\/\//i.test(val) || /^(data|blob):/i.test(val)) return val;

    // Root-relative path (e.g. /storage/products/x.jpg)
    if (val.startsWith('/')) return val;

    // Stored under Laravel storage symlink
    if (val.startsWith('storage/')) return '/' + val;
    if (val.startsWith('products/') || val.startsWith('images/')) return '/storage/' + val;

    // Bare filename -> assume product images dir
    return '/storage/products/' + val;
}

export default getProductImageUrl;
