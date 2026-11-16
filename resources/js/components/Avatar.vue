<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '' },
    size: { type: Number, default: 28 },
});

const palette = ['#116c78', '#ee6772', '#c6bd1f', '#4f5d75', '#7a4ba2', '#2f8f6b', '#d98a2b'];

const initials = computed(() => props.name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join(''));

const color = computed(() => {
    const sum = [...props.name].reduce((total, char) => total + char.charCodeAt(0), 0);

    return palette[sum % palette.length];
});
</script>

<template>
    <span
        class="avatar"
        :style="{ width: `${size}px`, height: `${size}px`, background: color, fontSize: `${Math.round(size * 0.38)}px`, borderRadius: `${Math.round(size * 0.3)}px` }"
        :title="name"
    >
        {{ initials || '?' }}
    </span>
</template>
