<script>
export default {
    name: "CategoryTreeItem",
};
</script>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    category: { type: Object, required: true },
    level: { type: Number, default: 0 },
    onEdit: { type: Function, required: true },
    onDelete: { type: Function, required: true },
});

const isExpanded = ref(true);
const hasChildren = computed(() => {
    return !!(
        props.category.children_recursive &&
        props.category.children_recursive.length > 0
    );
});

function toggleExpand() {
    if (hasChildren.value) {
        isExpanded.value = !isExpanded.value;
    }
}
</script>

<template>
    <div>
        <div
            class="flex items-center justify-between py-2 px-3 hover:bg-gray-50 rounded group"
            :style="{ paddingLeft: `${level * 24 + 12}px` }"
        >
            <div class="flex items-center flex-1 min-w-0">
                <!-- Expand/Collapse Toggle -->
                <button
                    v-if="hasChildren"
                    @click="toggleExpand"
                    class="mr-2 text-gray-400 hover:text-gray-600 focus:outline-none"
                >
                    <svg
                        :class="{ 'rotate-90': isExpanded }"
                        class="w-4 h-4 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
                <div v-else class="w-4 mr-2"></div>

                <!-- Icon -->
                <span class="mr-2 text-lg">
                    {{ hasChildren ? (isExpanded ? "📂" : "📁") : "📄" }}
                </span>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 truncate">{{
                            category.name
                        }}</span>
                        <span
                            class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-mono"
                        >
                            {{ category.code }}
                        </span>
                        <span
                            :class="
                                category.is_active
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'
                            "
                            class="px-2 py-0.5 rounded-full text-xs font-semibold"
                        >
                            {{ category.is_active ? "Active" : "Inactive" }}
                        </span>
                    </div>
                    <p
                        v-if="category.description"
                        class="text-xs text-gray-500 truncate mt-0.5"
                    >
                        {{ category.description }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div
                class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
            >
                <button
                    @click="onEdit(category)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Edit
                </button>
                <button
                    @click="onDelete(category)"
                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                    Delete
                </button>
            </div>
        </div>

        <!-- Children -->
        <div v-if="hasChildren && isExpanded">
            <CategoryTreeItem
                v-for="child in category.children_recursive"
                :key="child.id"
                :category="child"
                :level="level + 1"
                :onEdit="onEdit"
                :onDelete="onDelete"
            />
        </div>
    </div>
</template>
