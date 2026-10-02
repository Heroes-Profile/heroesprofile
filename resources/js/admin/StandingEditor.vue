<template>
  <div :class="expanded ? 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(0,0,0,0.75)]' : 'mb-3'" @click.self="expanded = false">
    <div :class="expanded ? 'flex flex-col w-full max-w-[1100px] h-[85vh] bg-gray-dark' : 'bg-darken'">
      <div v-if="editor" class="flex flex-wrap gap-1 p-1 border-b border-gray-dark">
        <button
          v-for="tool in tools"
          :key="tool.label"
          type="button"
          :title="tool.title"
          @click="tool.run()"
          class="text-sm rounded px-2 py-1 transition-colors"
          :class="tool.active && tool.active() ? 'bg-teal text-white' : 'hover:bg-lighten'"
        >
          <span :class="tool.style">{{ tool.label }}</span>
        </button>
        <button
          type="button"
          :title="expanded ? 'Back to the console (Esc)' : 'Write in a larger window'"
          @click="toggleExpanded"
          class="text-sm rounded px-2 py-1 ml-auto transition-colors"
          :class="expanded ? 'bg-teal text-white hover:bg-lteal' : 'hover:bg-lighten'"
        >
          {{ expanded ? 'Done' : 'Expand' }}
        </button>
      </div>
      <editor-content
        :editor="editor"
        class="standing-text standing-editor p-2"
        :class="{ 'standing-editor-expanded flex-1 overflow-auto bg-darken p-4 text-base': expanded }"
      />
    </div>
  </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { Markdown } from '@tiptap/markdown';

const MARKDOWN = /(^|\n)\s*([-*+] |\d+\. |#{1,6} |> )|\*\*[^*\n]+\*\*|\[[^\]\n]+\]\([^)\n]+\)/;

// Kept to what the sanitizer allows, so nothing typed here is lost on save.
export default {
  name: 'StandingEditor',
  components: {
    EditorContent,
  },
  props: {
    modelValue: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue'],
  data(){
    return {
      editor: null,
      expanded: false,
    }
  },
  computed: {
    tools(){
      const chain = () => this.editor.chain().focus();

      return [
        { label: 'B', title: 'Bold', style: 'font-bold', run: () => chain().toggleBold().run(), active: () => this.editor.isActive('bold') },
        { label: 'I', title: 'Italic', style: 'italic', run: () => chain().toggleItalic().run(), active: () => this.editor.isActive('italic') },
        { label: 'U', title: 'Underline', style: 'underline', run: () => chain().toggleUnderline().run(), active: () => this.editor.isActive('underline') },
        { label: 'Link', title: 'Add or remove a link', run: () => this.setLink(), active: () => this.editor.isActive('link') },
        { label: '• List', title: 'Bulleted list', run: () => chain().toggleBulletList().run(), active: () => this.editor.isActive('bulletList') },
        { label: '1. List', title: 'Numbered list', run: () => chain().toggleOrderedList().run(), active: () => this.editor.isActive('orderedList') },
      ];
    },
  },
  watch: {
    // The console clears the field after an action, or when another account loads.
    modelValue(value){
      if(this.editor && value !== this.currentValue()){
        this.editor.commands.setContent(value || '', { emitUpdate: false });
      }
    },
  },
  mounted(){
    this.editor = new Editor({
      content: this.modelValue,
      extensions: [
        StarterKit.configure({
          heading: false,
          blockquote: false,
          code: false,
          codeBlock: false,
          horizontalRule: false,
          strike: false,
          link: {
            openOnClick: false,
            autolink: true,
            defaultProtocol: 'https',
          },
        }),
        Markdown,
      ],
      editorProps: {
        handlePaste: (view, event) => this.pasteMarkdown(event),
      },
      onUpdate: () => {
        this.$emit('update:modelValue', this.currentValue());
      },
    });
  },
  beforeUnmount(){
    this.closeExpanded();
    this.editor?.destroy();
  },
  methods: {
    toggleExpanded(){
      if(this.expanded){
        this.closeExpanded();
        return;
      }

      this.expanded = true;
      document.addEventListener('keydown', this.onKeydown);
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => this.editor.commands.focus());
    },
    closeExpanded(){
      this.expanded = false;
      document.removeEventListener('keydown', this.onKeydown);
      document.body.style.overflow = '';
    },
    onKeydown(event){
      if(event.key === 'Escape'){
        this.closeExpanded();
      }
    },
    // Text that looks like Markdown is converted even when HTML comes with it, since
    // code editors put both on the clipboard. Anything else pastes as normal.
    // Headings, quotes and code have no place in what the sanitizer keeps, so they
    // are flattened first rather than lost.
    pasteMarkdown(event){
      const text = event.clipboardData?.getData('text/plain') || '';

      if(!MARKDOWN.test(text)){
        return false;
      }

      const flattened = text
        .replace(/^```.*$/gm, '')
        .replace(/^#{1,6}\s+(.+)$/gm, '**$1**')
        .replace(/^>\s?/gm, '');

      this.editor.chain().focus().insertContent(flattened, { contentType: 'markdown' }).run();

      return true;
    },
    currentValue(){
      return this.editor.isEmpty ? '' : this.editor.getHTML();
    },
    setLink(){
      const previous = this.editor.getAttributes('link').href || '';
      const url = window.prompt('Link address', previous);

      if(url === null){
        return;
      }

      if(url.trim() === ''){
        this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
      }

      this.editor.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
    },
  },
}
</script>
