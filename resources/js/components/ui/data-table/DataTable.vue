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
    <div :class="cn('w-full bg-white', props.class)">
        <!-- SCROLL HORIZONTAL HANYA DI SINI -->
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="">
                    <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <th v-for="header in headerGroup.headers" :key="header.id"
                            class="border-b border-slate-200 px-6 py-3 font-semibold"
                            :class="(header.column.columnDef.meta as DataTableColumnMeta | undefined)?.headerClass">
                            <template v-if="!header.isPlaceholder">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                            </template>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr v-for="row in table.getRowModel().rows" :key="row.id"
                        class="border-b border-slate-100 last:border-b-0 transition-colors duration-150 hover:bg-slate-50/80">
                        <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                            class="px-6 py-2 align-middle text-slate-700"
                            :class="(cell.column.columnDef.meta as DataTableColumnMeta | undefined)?.class">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </td>
                    </tr>

                    <tr v-if="table.getRowModel().rows.length === 0">
                        <td class="px-6 py-16 text-center" :colspan="columnLength">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm font-medium text-slate-500">
                                    Tidak ada data ditemukan
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
