/**
 * Order Status Enum
 */
export enum OrderStatus {
  PENDING_PAYMENT = 'pending_payment',
  PROCESSING = 'processing',
  READY_FOR_PICKUP = 'ready_for_pickup',
  SHIPPED = 'shipped',
  DELIVERED = 'delivered',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled',
}

/**
 * Payment Status Enum
 */
export enum PaymentStatus {
  PAID = 'paid',
  PENDING = 'pending',
  EXPIRED = 'expired',
  FAILED = 'failed',
}

/**
 * Order Status Configuration
 */
export const ORDER_STATUS_CONFIG: Record<
  OrderStatus,
  { class: string; bgClass: string; label: string }
> = {
  [OrderStatus.PENDING_PAYMENT]: {
    class: 'text-orange-700',
    bgClass: 'bg-orange-100',
    label: 'Menunggu Konfirmasi',
  },
  [OrderStatus.PROCESSING]: {
    class: 'text-blue-700',
    bgClass: 'bg-blue-100',
    label: 'Sedang Diproses',
  },
  [OrderStatus.READY_FOR_PICKUP]: {
    class: 'text-purple-700',
    bgClass: 'bg-purple-100',
    label: 'Siap Diambil',
  },
  [OrderStatus.SHIPPED]: {
    class: 'text-indigo-700',
    bgClass: 'bg-indigo-100',
    label: 'Dalam Pengiriman',
  },
  [OrderStatus.DELIVERED]: {
    class: 'text-emerald-700',
    bgClass: 'bg-emerald-100',
    label: 'Sudah Diterima',
  },
  [OrderStatus.COMPLETED]: {
    class: 'text-emerald-700',
    bgClass: 'bg-emerald-100',
    label: 'Selesai',
  },
  [OrderStatus.CANCELLED]: {
    class: 'text-slate-600',
    bgClass: 'bg-slate-100',
    label: 'Dibatalkan',
  },
};

/**
 * Payment Status Configuration
 */
export const PAYMENT_STATUS_CONFIG: Record<
  PaymentStatus,
  { class: string; bgClass: string; label: string }
> = {
  [PaymentStatus.PAID]: {
    class: 'text-emerald-700',
    bgClass: 'bg-emerald-100',
    label: 'Lunas',
  },
  [PaymentStatus.PENDING]: {
    class: 'text-amber-700',
    bgClass: 'bg-amber-100',
    label: 'Belum Dibayar',
  },
  [PaymentStatus.EXPIRED]: {
    class: 'text-slate-600',
    bgClass: 'bg-slate-100',
    label: 'Kedaluwarsa',
  },
  [PaymentStatus.FAILED]: {
    class: 'text-rose-700',
    bgClass: 'bg-rose-100',
    label: 'Gagal',
  },
};

/**
 * Helper function to get order status label
 */
export function getOrderStatusLabel(status: string): string {
  return ORDER_STATUS_CONFIG[status as OrderStatus]?.label || status;
}

/**
 * Helper function to get payment status label
 */
export function getPaymentStatusLabel(status: string): string {
  return PAYMENT_STATUS_CONFIG[status as PaymentStatus]?.label || status;
}
