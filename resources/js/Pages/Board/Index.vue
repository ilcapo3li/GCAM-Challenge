<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay } from '@headlessui/vue';
    import { Head, Link, useForm} from '@inertiajs/vue3';
    import Swal from 'sweetalert2';


    const props = defineProps({
        boards: {
            type: Object,
        },
        AddBoardModal: {
            type: Boolean,
        }
    });
    const form = useForm({
        id: null,
        title: null,
    });
    import { ref } from 'vue';
    import Vue3Datatable from '@bhplugin/vue3-datatable';
    const search1 = ref('');
    const datatable1Cols = ref([
        { field: 'id', title: 'ID' },
        { field: 'code', title: 'Code' },
        { field: 'title', title: 'Title' },
        { field: 'created_at', title: 'Created On' },
        { field: 'created_by', title: 'Created BY' },
        { field: 'action', title: 'Action', sort: false, headerClass: 'justify-center' },
    ]) || [];

    const formatDate = (date) => {
        if (date) {
            const dt = new Date(date);
            const month = dt.getMonth() + 1 < 10 ? '0' + (dt.getMonth() + 1) : dt.getMonth() + 1;
            const day = dt.getDate() < 10 ? '0' + dt.getDate() : dt.getDate();
            return day + '/' + month + '/' + dt.getFullYear();
        }
        return '';
    };

</script>

<template>
    <Head title="Boards" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Boards</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="panel pb-0">
                        <div class="flex md:items-center md:flex-row flex-col mb-5 gap-5">
                            <h5 class="font-semibold text-lg dark:text-white-light">Boards</h5>
                            <div class="ltr:ml-auto rtl:mr-auto">
                                <input v-model="search1" type="text" class="form-input w-auto" placeholder="Search..." />
                            </div>
                            <div>
                        <button type="button" class="btn btn-primary flex" @click="AddBoardModal = true">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24px"
                                height="24px"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="w-5 h-5 ltr:mr-3 rtl:ml-3"
                            >
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Project
                        </button>
                    </div>
                        </div>

                        <div class="datatable">
                                <vue3-datatable
                                    :rows="boards"
                                    :key="key"
                                    :columns="datatable1Cols"
                                    :totalRows="boards?.length"
                                    :sortable="true"
                                    sortColumn="title"
                                    :search="search1"
                                    skin="whitespace-nowrap table-hover"
                                    firstArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                                    lastArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg> '
                                    previousArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                                    nextArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                                >
                                    <template #title="data">
                                        <Link class="flex items-center w-max underline" :href="route('board.todo', data.value.id)">
                                            {{ data.value.title}}
                                        </Link>
                                    </template>
                                    <template #created_by="data">
                                        <Link class="flex items-center w-max" :href="route('board.todo', data.value.id)">
                                            <img class="w-9 h-9 rounded-full ltr:mr-2 rtl:ml-2 object-cover" :src="`/assets/images/profile-${Math.floor(Math.random() * 32) + 1}.jpeg`" />
                                            {{ data.value.created_by.name}}
                                        </Link>
                                    </template>
                                    <template #created_at="data">
                                        {{ formatDate(data.value.created_at) }}
                                    </template>
                                    <template #action>
                                        <div class="text-center">
                                            <button type="button" v-tippy>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                                                    <circle opacity="0.5" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                                                    <path d="M14.5 9.50002L9.5 14.5M9.49998 9.5L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                            <!-- add project modal -->
                                            <TransitionRoot appear :show="AddBoardModal" as="template">
                                                <Dialog as="div"  class="relative z-50">
                                                    <TransitionChild
                                                        as="template"
                                                        enter="duration-300 ease-out"
                                                        enter-from="opacity-0"
                                                        enter-to="opacity-100"
                                                        leave="duration-200 ease-in"
                                                        leave-from="opacity-100"
                                                        leave-to="opacity-0"
                                                    >
                                                        <DialogOverlay class="fixed inset-0 bg-[black]/60" />
                                                    </TransitionChild>

                                                    <div class="fixed inset-0 overflow-y-auto">
                                                        <div class="flex min-h-full items-center justify-center px-4 py-8">
                                                            <TransitionChild
                                                                as="template"
                                                                enter="duration-300 ease-out"
                                                                enter-from="opacity-0 scale-95"
                                                                enter-to="opacity-100 scale-100"
                                                                leave="duration-200 ease-in"
                                                                leave-from="opacity-100 scale-100"
                                                                leave-to="opacity-0 scale-95"
                                                            >
                                                                <DialogPanel class="panel border-0 p-0 rounded-lg overflow-hidden w-full max-w-lg text-black dark:text-white-dark">
                                                                    <button
                                                                        type="button"
                                                                        class="absolute top-4 ltr:right-4 rtl:left-4 text-gray-400 hover:text-gray-800 dark:hover:text-gray-600 outline-none"
                                                                        @click="AddBoardModal = false"
                                                                    >
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="24px"
                                                                            height="24px"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="w-6 h-6"
                                                                        >
                                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                                        </svg>
                                                                    </button>
                                                                    <div class="text-lg font-medium bg-[#fbfbfb] dark:bg-[#121c2c] ltr:pl-5 rtl:pr-5 py-3 ltr:pr-[50px] rtl:pl-[50px]">
                                                                        {{ 'Add Project' }}
                                                                    </div>
                                                                    <div class="p-5">
                                                                        <form @submit.prevent="form.post(route('board.store'))" class="mt-6 space-y-6">

                                                                            <div class="grid gap-5">
                                                                                <div>
                                                                                    <label for="title">Title</label>
                                                                                    <input id="title" v-model="form.title" type="text" class="form-input mt-1" placeholder="Enter Board Title" />
                                                                                    <div v-if="form.errors.title">{{ form.errors.title }}</div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="flex justify-end items-center mt-8">
                                                                                <button type="button" class="btn btn-outline-danger" @click="AddBoardModal = false">Cancel</button>
                                                                                <button type="submit" class="btn btn-primary ltr:ml-4 rtl:mr-4">{{ 'Add' }}</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </DialogPanel>
                                                            </TransitionChild>
                                                        </div>
                                                    </div>
                                                </Dialog>
                                            </TransitionRoot>
                                        </div>
                                    </template>
                            </vue3-datatable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
