export function formatRupiah(value) {
    if (value === null || value === undefined) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
}

export function parseCurrency(value) {
    if (typeof value === "number") return value;
    if (!value) return 0;
    return parseFloat(String(value).replace(/[^0-9.-]+/g, "")) || 0;
}
