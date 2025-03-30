declare module '@/components/ui/switch' {
  import { DefineComponent } from 'vue'
  
  export const Switch: DefineComponent<{
    modelValue?: boolean
    [key: string]: any
  }>
}
