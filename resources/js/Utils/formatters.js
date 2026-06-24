// Currency Formatting
export const formatCurrency = (value, options = {}) => {
    const {
        currency = "IDR",
        locale = "id-ID",
        minimumFractionDigits = 0,
        maximumFractionDigits = 2,
    } = options;

    if (value === null || value === undefined || value === "") return "-";

    const numValue = typeof value === "string" ? parseFloat(value) : value;
    if (isNaN(numValue)) return "-";

    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency,
        minimumFractionDigits,
        maximumFractionDigits,
    }).format(numValue);
};

// Currency without symbol (for display)
export const formatCurrencyValue = (value) => {
    if (value === null || value === undefined || value === "") return "0";

    const numValue = typeof value === "string" ? parseFloat(value) : value;
    if (isNaN(numValue)) return "0";

    return numValue.toLocaleString("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
};

// Parse currency input
export const parseCurrency = (value) => {
    if (!value) return 0;
    const numValue =
        typeof value === "string"
            ? parseFloat(value.replace(/[^\d.-]/g, ""))
            : value;
    return isNaN(numValue) ? 0 : numValue;
};

// Date Formatting
export const formatDate = (date, options = {}) => {
    const {
        format = "dd MMM yyyy", // 'dd MMM yyyy', 'yyyy-MM-dd', etc
        locale = "id-ID",
    } = options;

    if (!date) return "-";

    const dateObj = typeof date === "string" ? new Date(date) : date;
    if (isNaN(dateObj.getTime())) return "-";

    const formatter = new Intl.DateTimeFormat(locale, {
        year: "numeric",
        month: format.includes("MMM") ? "long" : "2-digit",
        day: "2-digit",
    });

    return formatter.format(dateObj);
};

// Time Formatting
export const formatTime = (date, options = {}) => {
    const { locale = "id-ID", format24h = true } = options;

    if (!date) return "-";

    const dateObj = typeof date === "string" ? new Date(date) : date;
    if (isNaN(dateObj.getTime())) return "-";

    const formatter = new Intl.DateTimeFormat(locale, {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: !format24h,
    });

    return formatter.format(dateObj);
};

// DateTime Formatting
export const formatDateTime = (date, options = {}) => {
    const { locale = "id-ID" } = options;

    if (!date) return "-";

    const dateObj = typeof date === "string" ? new Date(date) : date;
    if (isNaN(dateObj.getTime())) return "-";

    const dateFormatter = new Intl.DateTimeFormat(locale, {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });

    const timeFormatter = new Intl.DateTimeFormat(locale, {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });

    return `${dateFormatter.format(dateObj)} ${timeFormatter.format(dateObj)}`;
};

// Relative time (e.g., "2 days ago")
export const formatRelativeTime = (date, locale = "id-ID") => {
    if (!date) return "-";

    const dateObj = typeof date === "string" ? new Date(date) : date;
    if (isNaN(dateObj.getTime())) return "-";

    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: "auto" });
    const now = new Date();
    const diffMs = dateObj.getTime() - now.getTime();
    const diffSec = Math.round(diffMs / 1000);

    const intervals = [
        { name: "year", ms: 31536000 },
        { name: "month", ms: 2592000 },
        { name: "day", ms: 86400 },
        { name: "hour", ms: 3600 },
        { name: "minute", ms: 60 },
        { name: "second", ms: 1 },
    ];

    for (const interval of intervals) {
        const count = Math.round(diffSec / interval.ms);
        if (Math.abs(count) >= 1) {
            return rtf.format(count, interval.name);
        }
    }

    return "sekarang";
};

// Parse date string
export const parseDate = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    return isNaN(date.getTime()) ? null : date;
};
