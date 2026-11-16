import { ref, watch } from 'vue';
import type { Ref, WatchSource } from 'vue';

export function useFlashOnChange(
    source: WatchSource,
    durationMs = 700,
): Ref<boolean> {
    const flashing = ref(false);
    let timer: ReturnType<typeof setTimeout> | undefined;

    watch(source, () => {
        flashing.value = true;
        clearTimeout(timer);
        timer = setTimeout(() => {
            flashing.value = false;
        }, durationMs);
    });

    return flashing;
}
