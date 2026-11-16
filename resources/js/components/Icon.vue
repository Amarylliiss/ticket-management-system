<script setup>
defineProps({
    name: { type: String, required: true },
    size: { type: Number, default: 18 },
});

const paths = {
    grid: 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
    ticket: 'M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4zM10 5v12',
    inbox: 'M4 6h16v12H4zM4 7l8 6 8-6',
    flag: 'M5 21V4M5 4h11l-2 4 2 4H5',
    tag: 'M3 12V4h8l10 10-8 8zM7.5 7.5h.01',
    plus: 'M12 5v14M5 12h14',
    search: 'M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14zM20 20l-4-4',
    bell: 'M6 16V11a6 6 0 1 1 12 0v5l2 2H4zM10 21h4',
    left: 'M15 6l-6 6 6 6',
    right: 'M9 6l6 6-6 6',
    down: 'M6 9l6 6 6-6',
    clip: 'M20 11l-8.5 8.5a5 5 0 0 1-7-7L13 4a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L14 7',
    chat: 'M4 5h16v11H9l-5 4z',
    dots: 'M12 5h.01M12 12h.01M12 19h.01',
    send: 'M4 12l16-8-6 16-2-7z',
    info: 'M12 21a9 9 0 1 1 0-18 9 9 0 0 1 0 18zM12 11v5M12 8h.01',
    activity: 'M8 4h8v3H8zM6 6H5v15h14V6h-1M9 12h6M9 16h4',
    lock: 'M6 11h12v10H6zM8 11V8a4 4 0 0 1 8 0v3',
    logout: 'M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10',
    filter: 'M4 6h16l-6 7v5l-4 2v-7z',
    crown: 'M4 18h16l1-11-5 4-4-6-4 6-5-4z',
    users: 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2 21a7 7 0 0 1 14 0M17 11a3 3 0 1 0 0-6M22 20a5 5 0 0 0-4-5',
};
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.7"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path :d="paths[name]" />
    </svg>
</template>
