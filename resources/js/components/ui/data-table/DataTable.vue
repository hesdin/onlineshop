<script setup lang="ts">
import type { ColumnDef } from "@tanstack/vue-table"
import { FlexRender, getCoreRowModel, useVueTable } from "@tanstack/vue-table"
import type { HTMLAttributes } from "vue"
import { computed } from "vue"
import { cn } from "@/lib/utils"

type DataTableColumnMeta = {
    class?: string
    headerClass?: string
}

const props = defineProps<{
    columns: ColumnDef<any, any>[]
    data: any[]
    getRowId?: (originalRow: any, index: number, parent?: any) => string
    class?: HTMLAttributes["class"]
}>()

const table = useVueTable({
    get data() {
        return props.data ?? []
    },
    get columns() {
        return props.columns ?? []
    },
    getCoreRowModel: getCoreRowModel(),
    getRowId: props.getRowId,
})

const columnLength = computed(() => table.getAllColumns().length || 1)
</script>

<template>
    <div :class="cn('w-full rounded-lg border border-slate-200 bg-white', props.class)">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id"
                        class="border-b border-slate-200">
                        <th v-for="header in headerGroup.headers" :key="header.id"
                            class="px-6 py-4 font-semibold text-slate-700 tracking-wide uppercase text-xs"
                            :class="(header.column.columnDef.meta as DataTableColumnMeta | undefined)?.headerClass">
                            <template v-if="!header.isPlaceholder">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                            </template>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in table.getRowModel().rows" :key="row.id"
                        class="transition-colors duration-150 hover:bg-slate-50/70 group">
                        <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                            class="px-6 py-4 text-slate-600 align-middle"
                            :class="(cell.column.columnDef.meta as DataTableColumnMeta | undefined)?.class">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </td>
                    </tr>
                    <tr v-if="table.getRowModel().rows.length === 0">
                        <td class="px-6 py-16 text-center" :colspan="columnLength">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm font-medium text-slate-500">Tidak ada data ditemukan</p>
                                <p class="text-xs text-slate-400">Data akan muncul di sini setelah ditambahkan</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
