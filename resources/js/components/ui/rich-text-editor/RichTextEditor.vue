<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { watch, onBeforeUnmount } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  modelValue?: string;
  placeholder?: string;
  disabled?: boolean;
  class?: string;
  error?: boolean;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const editor = useEditor({
  content: props.modelValue || '',
  editable: !props.disabled,
  extensions: [
    StarterKit.configure({
      heading: false,
      codeBlock: false,
      blockquote: false,
      horizontalRule: false,
    }),
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm max-w-none focus:outline-none min-h-[120px] px-3 py-2',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

watch(
  () => props.modelValue,
  (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
      editor.value.commands.setContent(value || '', { emitUpdate: false });
    }
  },
);

watch(
  () => props.disabled,
  (disabled) => {
    editor.value?.setEditable(!disabled);
  },
);

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>

<template>
  <div :class="cn(
    'rounded-md border bg-white text-sm ring-offset-background',
    'focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2',
    error ? 'border-red-500' : 'border-input',
    disabled ? 'opacity-50 cursor-not-allowed' : '',
    props.class,
  )">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 px-2 py-1.5 bg-slate-50/50">
      <button type="button" :disabled="disabled" :class="[
        'inline-flex h-7 w-7 items-center justify-center rounded text-sm font-medium transition-colors',
        'hover:bg-slate-200 disabled:pointer-events-none disabled:opacity-50',
        editor?.isActive('bold') ? 'bg-slate-200 text-slate-900' : 'text-slate-600',
      ]" @click="editor?.chain().focus().toggleBold().run()" title="Bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
          <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
        </svg>
      </button>

      <button type="button" :disabled="disabled" :class="[
        'inline-flex h-7 w-7 items-center justify-center rounded text-sm font-medium transition-colors',
        'hover:bg-slate-200 disabled:pointer-events-none disabled:opacity-50',
        editor?.isActive('italic') ? 'bg-slate-200 text-slate-900' : 'text-slate-600',
      ]" @click="editor?.chain().focus().toggleItalic().run()" title="Italic">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" x2="10" y1="4" y2="4" />
          <line x1="14" x2="5" y1="20" y2="20" />
          <line x1="15" x2="9" y1="4" y2="20" />
        </svg>
      </button>

      <button type="button" :disabled="disabled" :class="[
        'inline-flex h-7 w-7 items-center justify-center rounded text-sm font-medium transition-colors',
        'hover:bg-slate-200 disabled:pointer-events-none disabled:opacity-50',
        editor?.isActive('strike') ? 'bg-slate-200 text-slate-900' : 'text-slate-600',
      ]" @click="editor?.chain().focus().toggleStrike().run()" title="Strikethrough">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 4H9a3 3 0 0 0-2.83 4" />
          <path d="M14 12a4 4 0 0 1 0 8H6" />
          <line x1="4" x2="20" y1="12" y2="12" />
        </svg>
      </button>

      <div class="mx-1 h-5 w-px bg-slate-200"></div>

      <button type="button" :disabled="disabled" :class="[
        'inline-flex h-7 w-7 items-center justify-center rounded text-sm font-medium transition-colors',
        'hover:bg-slate-200 disabled:pointer-events-none disabled:opacity-50',
        editor?.isActive('bulletList') ? 'bg-slate-200 text-slate-900' : 'text-slate-600',
      ]" @click="editor?.chain().focus().toggleBulletList().run()" title="Bullet List">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="8" x2="21" y1="6" y2="6" />
          <line x1="8" x2="21" y1="12" y2="12" />
          <line x1="8" x2="21" y1="18" y2="18" />
          <line x1="3" x2="3.01" y1="6" y2="6" />
          <line x1="3" x2="3.01" y1="12" y2="12" />
          <line x1="3" x2="3.01" y1="18" y2="18" />
        </svg>
      </button>

      <button type="button" :disabled="disabled" :class="[
        'inline-flex h-7 w-7 items-center justify-center rounded text-sm font-medium transition-colors',
        'hover:bg-slate-200 disabled:pointer-events-none disabled:opacity-50',
        editor?.isActive('orderedList') ? 'bg-slate-200 text-slate-900' : 'text-slate-600',
      ]" @click="editor?.chain().focus().toggleOrderedList().run()" title="Numbered List">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="10" x2="21" y1="6" y2="6" />
          <line x1="10" x2="21" y1="12" y2="12" />
          <line x1="10" x2="21" y1="18" y2="18" />
          <path d="M4 6h1v4" />
          <path d="M4 10h2" />
          <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1" />
        </svg>
      </button>
    </div>

    <!-- Editor Content -->
    <EditorContent :editor="editor" />
  </div>
</template>

<style>
.ProseMirror {
  min-height: 120px;
  outline: none;
}

.ProseMirror p {
  margin: 0.5em 0;
}

.ProseMirror p:first-child {
  margin-top: 0;
}

.ProseMirror p:last-child {
  margin-bottom: 0;
}

.ProseMirror ul,
.ProseMirror ol {
  padding-left: 1.5em;
  margin: 0.5em 0;
}

.ProseMirror li {
  margin: 0.25em 0;
}

.ProseMirror p.is-editor-empty:first-child::before {
  color: #9ca3af;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
