import { defineComponent, h } from 'vue'

const Select = defineComponent({
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    }
  },
  emits: ['update:modelValue'],
  setup(props, { slots, emit }) {
    return () => h('div', { class: 'relative w-full' }, slots.default?.())
  }
})

const SelectTrigger = defineComponent({
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  setup(props, { slots }) {
    return () => h('div', { 
      class: `flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${props.class}` 
    }, slots.default?.())
  }
})

const SelectValue = defineComponent({
  props: {
    placeholder: {
      type: String,
      default: 'Select an option'
    }
  },
  setup(props, { slots }) {
    return () => h('span', { class: 'block truncate' }, slots.default?.() || props.placeholder)
  }
})

const SelectContent = defineComponent({
  setup(props, { slots }) {
    return () => h('div', { 
      class: 'absolute z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md animate-in fade-in-80 mt-1 w-full' 
    }, slots.default?.())
  }
})

const SelectItem = defineComponent({
  props: {
    value: {
      type: [String, Number],
      required: true
    }
  },
  setup(props, { slots, emit }) {
    return () => h('div', { 
      class: 'relative flex cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      onClick: () => {
        const select = document.querySelector('select')
        if (select) {
          select.value = props.value.toString()
          select.dispatchEvent(new Event('change'))
        }
      }
    }, slots.default?.())
  }
})

export {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
}
