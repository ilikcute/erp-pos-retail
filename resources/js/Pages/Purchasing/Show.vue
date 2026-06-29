<script setup>
import { router, Head, Link } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";

const props = defineProps({
    purchaseOrder: { type: Object, required: true },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        PENDING: 'bg-semantic-warning-soft text-semantic-warning',
        APPROVED: 'bg-brand-soft text-brand',
        POSTED: 'bg-semantic-success-soft text-semantic-success',
        CANCELLED: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};

function approve() {
    if (!confirm("Setujui Purchase Order ini?")) return;
    router.post(`/purchasing/${props.purchaseOrder.id}/approve`, {}, {
        preserveScroll: true,
    });
}

function cancel() {
    if (!confirm("Batalkan Purchase Order ini?")) return;
    router.post(`/purchasing/${props.purchaseOrder.id}/cancel`, {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Purchase Order Details" />

    <DashboardLayout>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center flex-wrap gap-md">
            <div>
                <div class="flex items-center gap-md">
                    <h1 class="text-2xl font-bold text-ink-primary">
                        PO Details: <span class="font-mono text-brand">{{ purchaseOrder.po_number }}</span>
                    </h1>
                    <span :class="getStatusClass(purchaseOrder.status)" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                        {{ purchaseOrder.status }}
                    </span>
                </div>
                <p class="text-ink-secondary text-sm mt-xs">Dibuat oleh {{ purchaseOrder.creator?.name || 'System' }} pada {{ formatDate(purchaseOrder.created_at) }}</p>
            </div>
            
            <div class="flex gap-md">
                <Link href="/purchasing/po">
                    <BaseButton variant="secondary">Back to List</BaseButton>
                </Link>
                <BaseButton v-if="purchaseOrder.status === 'DRAFT'" @click="approve" variant="primary">
                    ✓ Approve PO
                </BaseButton>
                <BaseButton v-if="purchaseOrder.status === 'DRAFT' || purchaseOrder.status === 'APPROVED'" @click="cancel" variant="danger">
                    ✕ Cancel PO
                </BaseButton>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-xl mb-xl">
            <!-- Supplier info -->
            <div class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft">
                <h3 class="text-sm font-semibold text-ink-muted uppercase mb-md">Supplier Details 🏢</h3>
                <div class="space-y-sm">
                    <div class="font-bold text-ink-primary text-base">{{ purchaseOrder.supplier?.supplier_name }}</div>
                    <div class="text-sm text-ink-secondary">Code: {{ purchaseOrder.supplier?.supplier_code || '-' }}</div>
                    <div class="text-sm text-ink-secondary">Contact: {{ purchaseOrder.supplier?.contact_phone || purchaseOrder.supplier?.phone || '-' }}</div>
                </div>
            </div>

            <!-- Dates info -->
            <div class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft">
                <h3 class="text-sm font-semibold text-ink-muted uppercase mb-md">Date Information 📅</h3>
                <div class="grid grid-cols-2 gap-md text-sm">
                    <div>
                        <span class="text-ink-secondary block">Order Date</span>
                        <span class="font-semibold text-ink-primary">{{ formatDate(purchaseOrder.order_date) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-secondary block">Expected Date</span>
                        <span class="font-semibold text-ink-primary">{{ formatDate(purchaseOrder.expected_date) }}</span>
                    </div>
                </div>
            </div>

            <!-- Approver info -->
            <div class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft">
                <h3 class="text-sm font-semibold text-ink-muted uppercase mb-md">Approval Information 🔐</h3>
                <div v-if="purchaseOrder.approved_by" class="space-y-sm text-sm">
                    <div>
                        <span class="text-ink-secondary block">Approved By</span>
                        <span class="font-semibold text-ink-primary">{{ purchaseOrder.approver?.name }}</span>
                    </div>
                    <div>
                        <span class="text-ink-secondary block">Approved At</span>
                        <span class="font-semibold text-ink-primary">{{ formatDate(purchaseOrder.approved_at) }}</span>
                    </div>
                </div>
                <div v-else class="text-sm text-ink-secondary flex items-center h-full pb-6">
                    Waiting for approval.
                </div>
            </div>
        </div>

        <!-- Remarks Section -->
        <div v-if="purchaseOrder.remarks" class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft mb-xl">
            <h3 class="text-sm font-semibold text-ink-muted uppercase mb-sm">Remarks / Notes 📝</h3>
            <p class="text-sm text-ink-primary bg-surface-subtle p-md rounded border border-border-soft/50 font-serif">{{ purchaseOrder.remarks }}</p>
        </div>

        <!-- Items Table -->
        <div class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft space-y-md mb-xl">
            <h2 class="text-lg font-bold text-ink-primary">Purchase Items 📦</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-ink-primary">
                    <thead class="bg-surface-subtle text-ink-secondary uppercase text-xs">
                        <tr>
                            <th class="p-md">No</th>
                            <th class="p-md">Product / Variant</th>
                            <th class="p-md text-right">Qty Ordered</th>
                            <th class="p-md text-right">Qty Received</th>
                            <th class="p-md text-right">Unit Cost</th>
                            <th class="p-md text-right">Discount</th>
                            <th class="p-md text-right">Tax</th>
                            <th class="p-md text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <tr v-for="(item, idx) in purchaseOrder.items" :key="item.id">
                            <td class="p-md font-mono text-xs text-ink-muted">{{ idx + 1 }}</td>
                            <td class="p-md">
                                <span class="font-bold block">{{ item.product_variant?.product?.product_name }}</span>
                                <span class="text-xs text-ink-secondary font-mono">{{ item.product_variant?.sku }} ({{ item.product_variant?.variant_name || 'Default' }})</span>
                            </td>
                            <td class="p-md text-right font-mono">{{ item.ordered_qty }}</td>
                            <td class="p-md text-right font-mono">{{ item.received_qty }}</td>
                            <td class="p-md text-right font-mono">{{ formatCurrency(item.unit_cost) }}</td>
                            <td class="p-md text-right font-mono text-semantic-danger">-{{ formatCurrency(item.discount_amount) }}</td>
                            <td class="p-md text-right font-mono text-brand">+{{ formatCurrency(item.tax_amount) }}</td>
                            <td class="p-md text-right font-mono font-semibold">{{ formatCurrency(item.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Box -->
            <div class="flex justify-end pt-md border-t border-border-soft">
                <div class="w-80 space-y-sm text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-secondary">Subtotal</span>
                        <span class="font-mono font-medium">{{ formatCurrency(purchaseOrder.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-semantic-danger">
                        <span>Discount Total</span>
                        <span class="font-mono font-medium">-{{ formatCurrency(purchaseOrder.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-brand">
                        <span>Tax Total</span>
                        <span class="font-mono font-medium">+{{ formatCurrency(purchaseOrder.tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-border-soft pt-sm">
                        <span class="text-ink-primary">Grand Total</span>
                        <span class="font-mono text-brand">{{ formatCurrency(purchaseOrder.total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
