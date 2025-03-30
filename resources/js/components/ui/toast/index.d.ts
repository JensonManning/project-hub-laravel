declare module '@/components/ui/toast' {
  export interface ToastProps {
    title?: string;
    description?: string;
    variant?: 'default' | 'destructive';
    duration?: number;
  }

  export function useToast(): {
    toast: (props: ToastProps) => void;
    dismiss: (toastId?: string) => void;
  };
}
