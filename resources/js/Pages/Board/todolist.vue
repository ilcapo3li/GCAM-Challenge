<script lang="ts" setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { ref, onMounted } from 'vue';
    import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay } from '@headlessui/vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import 'vue3-quill/lib/vue3-quill.css';
    import Swal from 'sweetalert2';


    const props = defineProps({
        board: {
            type: Object,
        },
        addTaskModal:
        {
            type: Boolean,
        }
    });

    const form = useForm({
        title: null,
    });

    const defaultParams = ref({
        id: null,
        title: '',
        description: '',
        descriptionText: '',
        assignee: '',
        path: '',
        tag: '',
        priority: 'low',
    });

    const selectedTab = ref('');
    const isShowTaskMenu = ref(false);
    const addTaskModal = ref(props.addTaskModal);
    const viewTaskModal = ref(false);
    const params = ref(JSON.parse(JSON.stringify(defaultParams.value)));
    const allTasks = ref(props.board.board_items)
    const filteredTasks: any = ref([]);
    const pagedTasks: any = ref([]);
    const searchTask = ref('');
    const selectedTask: any = ref(defaultParams.value);
    const isPriorityMenu: any = ref(null);
    const isTagMenu: any = ref(null);

    const pager = ref({
        currentPage: 1,
        totalPages: 0,
        pageSize: 10,
        startIndex: 0,
        endIndex: 0,
    });

    const editorOptions = ref({
        modules: {
            toolbar: [[{ header: [1, 2, false] }], ['bold', 'italic', 'underline', 'link'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']],
        },
        placeholder: '',
    });

    onMounted(() => {
        searchTasks();
    });



    const searchTasks = (isResetPage = true) => {
        if (isResetPage) {
            pager.value.currentPage = 1;
        }
        let res;
        if (selectedTab.value === 'completed' || selectedTab.value === 'important' || selectedTab.value === 'trash') {
            res = allTasks.value.filter((d) => d.status_id === selectedTab.value);
        } else {
            res = allTasks.value.filter((d) => d.status != 'trash');
        }

        if (selectedTab.value === 'team' || selectedTab.value === 'update') {
            res = res.filter((d) => d.tag === selectedTab.value);
        } else if (selectedTab.value === 'high' || selectedTab.value === 'medium' || selectedTab.value === 'low') {
            res = res.filter((d) => d.priority === selectedTab.value);
        }
        filteredTasks.value = res.filter((d) => d.title?.toLowerCase().includes(searchTask.value));
        getPager();
    };

    const getPager = () => {
        setTimeout(() => {
            if (filteredTasks.value.length) {
                pager.value.totalPages = pager.value.pageSize < 1 ? 1 : Math.ceil(filteredTasks.value.length / pager.value.pageSize);
                if (pager.value.currentPage > pager.value.totalPages) {
                    pager.value.currentPage = 1;
                }
                pager.value.startIndex = (pager.value.currentPage - 1) * pager.value.pageSize;
                pager.value.endIndex = Math.min(pager.value.startIndex + pager.value.pageSize - 1, filteredTasks.value.length - 1);
                pagedTasks.value = filteredTasks.value.slice(pager.value.startIndex, pager.value.endIndex + 1);
            } else {
                pagedTasks.value = [];
                pager.value.startIndex = -1;
                pager.value.endIndex = -1;
            }
        });
    };

    const tabChanged = (type: any = null) => {
        selectedTab.value = type;
        searchTasks();
        isShowTaskMenu.value = false;
    };

    const taskComplete = (task: any = null) => {
        let item = filteredTasks.value.find((d) => d.id === task.id);
        item.status_id = item.status_id == 3 ? '' : 3;
        searchTasks(false);
    };

    const viewTask = (item: any = null) => {
        selectedTask.value = item;
        setTimeout(() => {
            viewTaskModal.value = true;
        });
    };

    const addEditTask = (task: any = null) => {
        isShowTaskMenu.value = false;

        params.value = JSON.parse(JSON.stringify(defaultParams.value));

        if (task) {
            params.value = JSON.parse(JSON.stringify(task));
        }

        addTaskModal.value = true;
    };

    const deleteTask = (task: any, type: string = '') => {
        if (type === 'delete') {
            task.status = 'trash';
        }
        if (type === 'deletePermanent') {
            allTasks.value = allTasks.value.filter((d: any) => d.id != task.id);
        } else if (type === 'restore') {
            task.status = '';
        }
        searchTasks(false);
    };

    const saveTask = () => {
        if (!params.value.title) {
            showMessage('Title is required.', 'error');
            return false;
        }



        showMessage('Task has been saved successfully.');
        addTaskModal.value = false;
    };

    const showMessage = (msg = '', type = 'success') => {
        const toast: any = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
            customClass: { container: 'toast' },
        });
        toast.fire({
            icon: type,
            title: msg,
            padding: '10px 20px',
        });
    };

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
    <Head title="TODO LIST" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">TODO</h2>
        </template>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="flex gap-5 relative sm:h-[calc(100vh_-_150px)] h-full">
                <div
                    class="panel p-4 flex-none w-[240px] max-w-full absolute xl:relative z-10 space-y-4 xl:h-auto h-full xl:block ltr:xl:rounded-r-md ltr:rounded-r-none rtl:xl:rounded-l-md rtl:rounded-l-none hidden"
                    :class="{ '!block': isShowTaskMenu }"
                >
                    <div class="flex flex-col h-full pb-16">
                        <div class="pb-5">
                            <div class="flex text-center items-center">
                                <div>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                                        <path
                                            opacity="0.5"
                                            d="M16 4.00195C18.175 4.01406 19.3529 4.11051 20.1213 4.87889C21 5.75757 21 7.17179 21 10.0002V16.0002C21 18.8286 21 20.2429 20.1213 21.1215C19.2426 22.0002 17.8284 22.0002 15 22.0002H9C6.17157 22.0002 4.75736 22.0002 3.87868 21.1215C3 20.2429 3 18.8286 3 16.0002V10.0002C3 7.17179 3 5.75757 3.87868 4.87889C4.64706 4.11051 5.82497 4.01406 8 4.00195"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                        <path d="M8 14H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M7 10.5H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M9 17.5H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        <path
                                            d="M8 3.5C8 2.67157 8.67157 2 9.5 2H14.5C15.3284 2 16 2.67157 16 3.5V4.5C16 5.32843 15.3284 6 14.5 6H9.5C8.67157 6 8 5.32843 8 4.5V3.5Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold ltr:ml-3 rtl:mr-3">Todo list</h3>
                            </div>
                        </div>
                        <div class="h-px w-full border-b border-[#e0e6ed] dark:border-[#1b2e4b] mb-5"></div>
                        <perfect-scrollbar
                            :options="{
                                swipeEasing: true,
                                wheelPropagation: false,
                            }"
                            class="relative pr-3.5 -mr-3.5 h-full grow"
                        >
                            <div class="space-y-1">
                                <button
                                    type="button"
                                    class="w-full flex justify-between items-center p-2 hover:bg-white-dark/10 rounded-md dark:hover:text-primary hover:text-primary dark:hover:bg-[#181F32] font-medium h-10"
                                    :class="{ 'bg-gray-100 dark:text-primary text-primary dark:bg-[#181F32]': selectedTab === '' }"
                                    @click="tabChanged('')"
                                >
                                    <div class="flex items-center">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5">
                                            <path
                                                d="M2 5.5L3.21429 7L7.5 3"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                opacity="0.5"
                                                d="M2 12.5L3.21429 14L7.5 10"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M2 19.5L3.21429 21L7.5 17"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path d="M22 19L12 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                            <path opacity="0.5" d="M22 12L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                            <path d="M22 5L12 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                        <div class="ltr:ml-3 rtl:mr-3">TODO</div>
                                    </div>
                                    <div class="bg-primary-light dark:bg-[#060818] rounded-md py-0.5 px-2 font-semibold whitespace-nowrap">
                                        {{ allTasks && allTasks.filter((d) => d.status_id == 1).length }}
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    class="w-full flex justify-between items-center p-2 hover:bg-white-dark/10 rounded-md dark:hover:text-primary hover:text-primary dark:hover:bg-[#181F32] font-medium h-10"
                                    :class="{ 'bg-gray-100 dark:text-primary text-primary dark:bg-[#181F32]': selectedTab === 'completed' }"
                                    @click="tabChanged('complete')"
                                >
                                    <div class="flex items-center">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                                            <path
                                                d="M20.9751 12.1852L20.2361 12.0574L20.9751 12.1852ZM20.2696 16.265L19.5306 16.1371L20.2696 16.265ZM6.93776 20.4771L6.19055 20.5417H6.19055L6.93776 20.4771ZM6.1256 11.0844L6.87281 11.0198L6.1256 11.0844ZM13.9949 5.22142L14.7351 5.34269V5.34269L13.9949 5.22142ZM13.3323 9.26598L14.0724 9.38725V9.38725L13.3323 9.26598ZM6.69813 9.67749L6.20854 9.10933H6.20854L6.69813 9.67749ZM8.13687 8.43769L8.62646 9.00585H8.62646L8.13687 8.43769ZM10.518 4.78374L9.79207 4.59542L10.518 4.78374ZM10.9938 2.94989L11.7197 3.13821L11.7197 3.13821L10.9938 2.94989ZM12.6676 2.06435L12.4382 2.77841L12.4382 2.77841L12.6676 2.06435ZM12.8126 2.11093L13.0419 1.39687L13.0419 1.39687L12.8126 2.11093ZM9.86194 6.46262L10.5235 6.81599V6.81599L9.86194 6.46262ZM13.9047 3.24752L13.1787 3.43584V3.43584L13.9047 3.24752ZM11.6742 2.13239L11.3486 1.45675L11.3486 1.45675L11.6742 2.13239ZM20.2361 12.0574L19.5306 16.1371L21.0086 16.3928L21.7142 12.313L20.2361 12.0574ZM13.245 21.25H8.59634V22.75H13.245V21.25ZM7.68497 20.4125L6.87281 11.0198L5.37839 11.149L6.19055 20.5417L7.68497 20.4125ZM19.5306 16.1371C19.0238 19.0677 16.3813 21.25 13.245 21.25V22.75C17.0712 22.75 20.3708 20.081 21.0086 16.3928L19.5306 16.1371ZM13.2548 5.10015L12.5921 9.14472L14.0724 9.38725L14.7351 5.34269L13.2548 5.10015ZM7.18772 10.2456L8.62646 9.00585L7.64728 7.86954L6.20854 9.10933L7.18772 10.2456ZM11.244 4.97206L11.7197 3.13821L10.2678 2.76157L9.79207 4.59542L11.244 4.97206ZM12.4382 2.77841L12.5832 2.82498L13.0419 1.39687L12.897 1.3503L12.4382 2.77841ZM10.5235 6.81599C10.8354 6.23198 11.0777 5.61339 11.244 4.97206L9.79207 4.59542C9.65572 5.12107 9.45698 5.62893 9.20041 6.10924L10.5235 6.81599ZM12.5832 2.82498C12.8896 2.92342 13.1072 3.16009 13.1787 3.43584L14.6306 3.05921C14.4252 2.26719 13.819 1.64648 13.0419 1.39687L12.5832 2.82498ZM11.7197 3.13821C11.7547 3.0032 11.8522 2.87913 11.9998 2.80804L11.3486 1.45675C10.8166 1.71309 10.417 2.18627 10.2678 2.76157L11.7197 3.13821ZM11.9998 2.80804C12.1345 2.74311 12.2931 2.73181 12.4382 2.77841L12.897 1.3503C12.3872 1.18655 11.8312 1.2242 11.3486 1.45675L11.9998 2.80804ZM14.1537 10.9842H19.3348V9.4842H14.1537V10.9842ZM14.7351 5.34269C14.8596 4.58256 14.824 3.80477 14.6306 3.0592L13.1787 3.43584C13.3197 3.97923 13.3456 4.54613 13.2548 5.10016L14.7351 5.34269ZM8.59634 21.25C8.12243 21.25 7.726 20.887 7.68497 20.4125L6.19055 20.5417C6.29851 21.7902 7.34269 22.75 8.59634 22.75V21.25ZM8.62646 9.00585C9.30632 8.42 10.0391 7.72267 10.5235 6.81599L9.20041 6.10924C8.85403 6.75767 8.30249 7.30493 7.64728 7.86954L8.62646 9.00585ZM21.7142 12.313C21.9695 10.8365 20.8341 9.4842 19.3348 9.4842V10.9842C19.9014 10.9842 20.3332 11.4959 20.2361 12.0574L21.7142 12.313ZM12.5921 9.14471C12.4344 10.1076 13.1766 10.9842 14.1537 10.9842V9.4842C14.1038 9.4842 14.0639 9.43901 14.0724 9.38725L12.5921 9.14471ZM6.87281 11.0198C6.84739 10.7258 6.96474 10.4378 7.18772 10.2456L6.20854 9.10933C5.62021 9.61631 5.31148 10.3753 5.37839 11.149L6.87281 11.0198Z"
                                                fill="currentColor"
                                            />
                                            <path
                                                opacity="0.5"
                                                d="M3.9716 21.4709L3.22439 21.5355L3.9716 21.4709ZM3 10.2344L3.74721 10.1698C3.71261 9.76962 3.36893 9.46776 2.96767 9.48507C2.5664 9.50239 2.25 9.83274 2.25 10.2344L3 10.2344ZM4.71881 21.4063L3.74721 10.1698L2.25279 10.299L3.22439 21.5355L4.71881 21.4063ZM3.75 21.5129V10.2344H2.25V21.5129H3.75ZM3.22439 21.5355C3.2112 21.383 3.33146 21.2502 3.48671 21.2502V22.7502C4.21268 22.7502 4.78122 22.1281 4.71881 21.4063L3.22439 21.5355ZM3.48671 21.2502C3.63292 21.2502 3.75 21.3686 3.75 21.5129H2.25C2.25 22.1954 2.80289 22.7502 3.48671 22.7502V21.2502Z"
                                                fill="currentColor"
                                            />
                                        </svg>
                                        <div class="ltr:ml-3 rtl:mr-3">Completed</div>
                                    </div>
                                    <div class="bg-primary-light dark:bg-[#060818] rounded-md py-0.5 px-2 font-semibold whitespace-nowrap">
                                        {{ allTasks && allTasks.filter((d) => d.status_id == 3).length }}
                                    </div>
                                </button>


                                <div class="h-px w-full border-b border-[#e0e6ed] dark:border-[#1b2e4b]"></div>
                                <div class="text-white-dark px-1 py-3">Tags</div>
                                <button
                                    type="button"
                                    class="w-full flex items-center h-10 p-1 hover:bg-white-dark/10 rounded-md dark:hover:bg-[#181F32] font-medium text-success ltr:hover:pl-3 rtl:hover:pr-3 duration-300"
                                    :class="{ 'ltr:pl-3 rtl:pr-3 bg-gray-100 dark:bg-[#181F32]': selectedTab === 'team' }"
                                    @click="tabChanged('team')"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-3 h-3 rotate-45 fill-success"
                                    >
                                        <path
                                            d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <div class="ltr:ml-3 rtl:mr-3">Team</div>
                                </button>

                                <button
                                    type="button"
                                    class="w-full flex items-center h-10 p-1 hover:bg-white-dark/10 rounded-md dark:hover:bg-[#181F32] font-medium text-warning ltr:hover:pl-3 rtl:hover:pr-3 duration-300"
                                    :class="{ 'ltr:pl-3 rtl:pr-3 bg-gray-100 dark:bg-[#181F32]': selectedTab === 'low' }"
                                    @click="tabChanged('low')"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-3 h-3 rotate-45 fill-warning"
                                    >
                                        <path
                                            d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <div class="ltr:ml-3 rtl:mr-3">Low</div>
                                </button>

                                <button
                                    type="button"
                                    class="w-full flex items-center h-10 p-1 hover:bg-white-dark/10 rounded-md dark:hover:bg-[#181F32] font-medium text-primary ltr:hover:pl-3 rtl:hover:pr-3 duration-300"
                                    :class="{ 'ltr:pl-3 rtl:pr-3 bg-gray-100 dark:bg-[#181F32]': selectedTab === 'medium' }"
                                    @click="tabChanged('medium')"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-3 h-3 rotate-45 fill-primary"
                                    >
                                        <path
                                            d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <div class="ltr:ml-3 rtl:mr-3">Medium</div>
                                </button>

                                <button
                                    type="button"
                                    class="w-full flex items-center h-10 p-1 hover:bg-white-dark/10 rounded-md dark:hover:bg-[#181F32] font-medium text-danger ltr:hover:pl-3 rtl:hover:pr-3 duration-300"
                                    :class="{ 'ltr:pl-3 rtl:pr-3 bg-gray-100 dark:bg-[#181F32]': selectedTab === 'high' }"
                                    @click="tabChanged('high')"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-3 h-3 rotate-45 fill-danger"
                                    >
                                        <path
                                            d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <div class="ltr:ml-3 rtl:mr-3">High</div>
                                </button>

                                <button
                                    type="button"
                                    class="w-full flex items-center h-10 p-1 hover:bg-white-dark/10 rounded-md dark:hover:bg-[#181F32] font-medium text-info ltr:hover:pl-3 rtl:hover:pr-3 duration-300"
                                    :class="{ 'ltr:pl-3 rtl:pr-3 bg-gray-100 dark:bg-[#181F32]': selectedTab === 'update' }"
                                    @click="tabChanged('update')"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-3 h-3 rotate-45 fill-info"
                                    >
                                        <path
                                            d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <div class="ltr:ml-3 rtl:mr-3">Update</div>
                                </button>
                            </div>
                        </perfect-scrollbar>
                        <div class="ltr:left-0 rtl:right-0 absolute bottom-0 p-4 w-full">
                            <button class="btn btn-primary w-full" type="button" @click="addTaskModal = true">
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
                                    class="w-5 h-5 ltr:mr-2 rtl:ml-2"
                                >
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>

                                Add New Task
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    class="overlay bg-black/60 z-[5] w-full h-full rounded-md absolute hidden"
                    :class="{ '!block xl:!hidden': isShowTaskMenu }"
                    @click="isShowTaskMenu = !isShowTaskMenu"
                ></div>
                <div class="panel p-0 flex-1 overflow-auto h-full">
                    <div class="flex flex-col h-full">
                        <div class="p-4 flex sm:flex-row flex-col w-full sm:items-center gap-4">
                            <div class="ltr:mr-3 rtl:ml-3 flex items-center">
                                <button type="button" class="xl:hidden hover:text-primary block ltr:mr-3 rtl:ml-3" @click="isShowTaskMenu = !isShowTaskMenu">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
                                        <path d="M20 7L4 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        <path opacity="0.5" d="M20 12L4 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        <path d="M20 17L4 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div class="relative group flex-1">
                                    <input
                                        type="text"
                                        class="form-input peer ltr:!pr-10 rtl:!pl-10"
                                        placeholder="Search Task..."
                                        v-model="searchTask"
                                        @keyup="searchTasks()"
                                    />
                                    <div class="absolute ltr:right-[11px] rtl:left-[11px] top-1/2 -translate-y-1/2 peer-focus:text-primary">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4">
                                            <circle cx="11.5" cy="11.5" r="9.5" stroke="currentColor" stroke-width="1.5" opacity="0.5"></circle>
                                            <path d="M18.5 18.5L22 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center sm:justify-end sm:flex-auto flex-1">
                                <p class="ltr:mr-3 rtl:ml-3">
                                    {{ pager.startIndex + 1 + '-' + (pager.endIndex + 1) + ' of ' + filteredTasks.length }}
                                </p>
                                <button
                                    type="button"
                                    :disabled="pager.currentPage == 1"
                                    class="bg-[#f4f4f4] rounded-md p-1 enabled:hover:bg-primary-light dark:bg-white-dark/20 enabled:dark:hover:bg-white-dark/30 ltr:mr-3 rtl:ml-3 disabled:opacity-60 disabled:cursor-not-allowed"
                                    @click="pager.currentPage--, searchTasks(false)"
                                >
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 rtl:rotate-180">
                                        <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    :disabled="pager.currentPage == pager.totalPages"
                                    class="bg-[#f4f4f4] rounded-md p-1 enabled:hover:bg-primary-light dark:bg-white-dark/20 enabled:dark:hover:bg-white-dark/30 disabled:opacity-60 disabled:cursor-not-allowed"
                                    @click="pager.currentPage++, searchTasks(false)"
                                >
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ltr:rotate-180">
                                        <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="h-px w-full border-b border-[#e0e6ed] dark:border-[#1b2e4b]"></div>
                        <template v-if="pagedTasks.length">
                            <div class="table-responsive grow overflow-y-auto sm:min-h-[300px] min-h-[400px]">
                                <table class="table-hover">
                                    <tbody>
                                        <template v-for="task in pagedTasks" :key="task.id">
                                            <tr class="group cursor-pointer" :class="{ 'bg-white-light/30 dark:bg-[#1a2941]': task.status === 'complete' }">
                                                <td class="w-1">
                                                    <input
                                                        type="checkbox"
                                                        :id="`chk-${task.id}`"
                                                        class="form-checkbox"
                                                        :checked="task.status_id == 3"
                                                        @click.stop="taskComplete(task)"
                                                        :disabled="selectedTab === 'trash'"
                                                    />
                                                </td>
                                                <td>
                                                    <div @click="viewTask(task)">
                                                        <div
                                                            class="group-hover:text-primary font-semibold text-base whitespace-nowrap"
                                                            :class="{ 'line-through': task.status === 'complete' }"
                                                        >
                                                            {{ task.code }}
                                                        </div>
                                                        <div
                                                            class="text-white-dark overflow-hidden min-w-[300px] line-clamp-1"
                                                            :class="{ 'line-through': task.status === 'complete' }"
                                                        >
                                                            {{ task.title }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="w-1">
                                                    <div class="flex items-center ltr:justify-end rtl:justify-start space-x-2 rtl:space-x-reverse">
                                                        <template v-if="task.priority">
                                                            <div class="dropdown">
                                                                <Popper
                                                                    :placement="'bottom-end'"
                                                                    offsetDistance="0"
                                                                    class="align-middle"
                                                                    @open:popper="isPriorityMenu = task.id"
                                                                    @close:popper="isPriorityMenu = null"
                                                                >
                                                                    <a
                                                                        href="javascript:;"
                                                                        class="badge rounded-full capitalize hover:top-0 hover:text-white"
                                                                        :class="{
                                                                            'badge-outline-primary hover:bg-primary': task.priority === 'medium',
                                                                            'badge-outline-warning hover:bg-warning': task.priority === 'low',
                                                                            'badge-outline-danger hover:bg-danger': task.priority === 'high',
                                                                            'text-white bg-primary': task.priority === 'medium' && isPriorityMenu === task.id,
                                                                            'text-white bg-warning': task.priority === 'low' && isPriorityMenu === task.id,
                                                                            'text-white bg-danger': task.priority === 'high' && isPriorityMenu === task.id,
                                                                        }"
                                                                    >
                                                                        {{ task.priority }}
                                                                    </a>

                                                                </Popper>
                                                            </div>
                                                        </template>
                                                        <template v-if="task.tag">
                                                            <div class="dropdown">
                                                                <Popper
                                                                    :placement=" 'bottom-end'"
                                                                    offsetDistance="0"
                                                                    class="align-middle"
                                                                    @open:popper="isTagMenu = task.id"
                                                                    @close:popper="isTagMenu = null"
                                                                >
                                                                    <a
                                                                        href="javascript:;"
                                                                        class="badge rounded-full capitalize hover:top-0 hover:text-white"
                                                                        :class="{
                                                                            'badge-outline-success hover:bg-success': task.tag === 'team',
                                                                            'badge-outline-info hover:bg-info': task.tag === 'update',
                                                                            'text-white bg-success ': task.tag === 'team' && isTagMenu === task.id,
                                                                            'text-white bg-info ': task.tag === 'update' && isTagMenu === task.id,
                                                                        }"
                                                                    >
                                                                        {{ task.tag }}
                                                                    </a>

                                                                </Popper>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="w-1">
                                                    <p
                                                        class="whitespace-nowrap text-white-dark font-medium"
                                                        :class="{ 'line-through': task.status === 'complete' }"
                                                    >
                                                        {{ formatDate(task.created_at) }}
                                                    </p>
                                                </td>
                                                <td class="w-1">
                                                    <div class="flex items-center justify-between w-max">
                                                        <div class="ltr:mr-2.5 rtl:ml-2.5 flex-shrink-0">
                                                            <div v-if="task.path">
                                                                <img :src="`/assets/images/${task.path}`" class="h-8 w-8 rounded-full object-cover" alt="avatar" />
                                                            </div>
                                                            <div
                                                                v-if="!task.path && task.assignee"
                                                                class="grid place-content-center h-8 w-8 rounded-full bg-primary text-white text-sm font-semibold"
                                                            >
                                                                {{ task.assignee.charAt(0) + '' + task.assignee.charAt(task.assignee.indexOf(' ') + 1) }}
                                                            </div>
                                                            <div
                                                                v-if="!task.path && !task.assignee"
                                                                class="border border-gray-300 dark:border-gray-800 rounded-full grid place-content-center h-8 w-8"
                                                            >
                                                                <svg
                                                                    width="24"
                                                                    height="24"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4.5 h-4.5"
                                                                >
                                                                    <circle cx="12" cy="6" r="4" stroke="currentColor" stroke-width="1.5"></circle>
                                                                    <ellipse
                                                                        opacity="0.5"
                                                                        cx="12"
                                                                        cy="17"
                                                                        rx="7"
                                                                        ry="4"
                                                                        stroke="currentColor"
                                                                        stroke-width="1.5"
                                                                    ></ellipse>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <Popper
                                                                :placement="'bottom-end'"
                                                                offsetDistance="0"
                                                                class="align-middle"
                                                            >
                                                                <a href="javascript:;">
                                                                    <svg
                                                                        width="24"
                                                                        height="24"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-5 h-5 rotate-90 opacity-70"
                                                                    >
                                                                        <circle cx="5" cy="12" r="2" stroke="currentColor" stroke-width="1.5"></circle>
                                                                        <circle
                                                                            opacity="0.5"
                                                                            cx="12"
                                                                            cy="12"
                                                                            r="2"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                        ></circle>
                                                                        <circle cx="19" cy="12" r="2" stroke="currentColor" stroke-width="1.5"></circle>
                                                                    </svg>
                                                                </a>
                                                                <template #content="{ close }">
                                                                    <ul @click="close()" class="whitespace-nowrap">
                                                                        <template v-if="selectedTab !== 'trash'">
                                                                            <li>
                                                                                <a href="javascript:;" @click="addEditTask(task)">
                                                                                    <svg
                                                                                        width="24"
                                                                                        height="24"
                                                                                        viewBox="0 0 24 24"
                                                                                        fill="none"
                                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-4.5 h-4.5 ltr:mr-2 rtl:ml-2"
                                                                                    >
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M4 22H20"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        />
                                                                                        <path
                                                                                            d="M14.6296 2.92142L13.8881 3.66293L7.07106 10.4799C6.60933 10.9416 6.37846 11.1725 6.17992 11.4271C5.94571 11.7273 5.74491 12.0522 5.58107 12.396C5.44219 12.6874 5.33894 12.9972 5.13245 13.6167L4.25745 16.2417L4.04356 16.8833C3.94194 17.1882 4.02128 17.5243 4.2485 17.7515C4.47573 17.9787 4.81182 18.0581 5.11667 17.9564L5.75834 17.7426L8.38334 16.8675L8.3834 16.8675C9.00284 16.6611 9.31256 16.5578 9.60398 16.4189C9.94775 16.2551 10.2727 16.0543 10.5729 15.8201C10.8275 15.6215 11.0583 15.3907 11.5201 14.929L11.5201 14.9289L18.3371 8.11195L19.0786 7.37044C20.3071 6.14188 20.3071 4.14999 19.0786 2.92142C17.85 1.69286 15.8581 1.69286 14.6296 2.92142Z"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                        />
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M13.8879 3.66406C13.8879 3.66406 13.9806 5.23976 15.3709 6.63008C16.7613 8.0204 18.337 8.11308 18.337 8.11308M5.75821 17.7437L4.25732 16.2428"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                        />
                                                                                    </svg>

                                                                                    Edit
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a href="javascript:;" @click="deleteTask(task, 'delete')">
                                                                                    <svg
                                                                                        width="24"
                                                                                        height="24"
                                                                                        viewBox="0 0 24 24"
                                                                                        fill="none"
                                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-5 h-5 ltr:mr-2 rtl:ml-2"
                                                                                    >
                                                                                        <path
                                                                                            d="M20.5001 6H3.5"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M9.5 11L10 16"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M14.5 11L14 16"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M6.5 6C6.55588 6 6.58382 6 6.60915 5.99936C7.43259 5.97849 8.15902 5.45491 8.43922 4.68032C8.44784 4.65649 8.45667 4.62999 8.47434 4.57697L8.57143 4.28571C8.65431 4.03708 8.69575 3.91276 8.75071 3.8072C8.97001 3.38607 9.37574 3.09364 9.84461 3.01877C9.96213 3 10.0932 3 10.3553 3H13.6447C13.9068 3 14.0379 3 14.1554 3.01877C14.6243 3.09364 15.03 3.38607 15.2493 3.8072C15.3043 3.91276 15.3457 4.03708 15.4286 4.28571L15.5257 4.57697C15.5433 4.62992 15.5522 4.65651 15.5608 4.68032C15.841 5.45491 16.5674 5.97849 17.3909 5.99936C17.4162 6 17.4441 6 17.5 6"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                        ></path>
                                                                                    </svg>
                                                                                    Delete
                                                                                </a>
                                                                            </li>

                                                                        </template>
                                                                        <template v-if="selectedTab === 'trash'">
                                                                            <li>
                                                                                <a href="javascript:;" @click="deleteTask(task, 'deletePermanent')">
                                                                                    <svg
                                                                                        width="24"
                                                                                        height="24"
                                                                                        viewBox="0 0 24 24"
                                                                                        fill="none"
                                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-5 h-5 ltr:mr-2 rtl:ml-2"
                                                                                    >
                                                                                        <path
                                                                                            d="M20.5001 6H3.5"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M9.5 11L10 16"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M14.5 11L14 16"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                        ></path>
                                                                                        <path
                                                                                            opacity="0.5"
                                                                                            d="M6.5 6C6.55588 6 6.58382 6 6.60915 5.99936C7.43259 5.97849 8.15902 5.45491 8.43922 4.68032C8.44784 4.65649 8.45667 4.62999 8.47434 4.57697L8.57143 4.28571C8.65431 4.03708 8.69575 3.91276 8.75071 3.8072C8.97001 3.38607 9.37574 3.09364 9.84461 3.01877C9.96213 3 10.0932 3 10.3553 3H13.6447C13.9068 3 14.0379 3 14.1554 3.01877C14.6243 3.09364 15.03 3.38607 15.2493 3.8072C15.3043 3.91276 15.3457 4.03708 15.4286 4.28571L15.5257 4.57697C15.5433 4.62992 15.5522 4.65651 15.5608 4.68032C15.841 5.45491 16.5674 5.97849 17.3909 5.99936C17.4162 6 17.4441 6 17.5 6"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                        ></path>
                                                                                    </svg>
                                                                                    Permanent Delete
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a href="javascript:;" @click="deleteTask(task, 'restore')">
                                                                                    <svg
                                                                                        width="24"
                                                                                        height="24"
                                                                                        viewBox="0 0 24 24"
                                                                                        fill="none"
                                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-5 h-5 ltr:mr-2 rtl:ml-2"
                                                                                    >
                                                                                        <g clip-path="url(#clip0_1276_6232)">
                                                                                            <path
                                                                                                d="M19.7285 10.9288C20.4413 13.5978 19.7507 16.5635 17.6569 18.6573C14.5327 21.7815 9.46736 21.7815 6.34316 18.6573C3.21897 15.5331 3.21897 10.4678 6.34316 7.3436C9.46736 4.21941 14.5327 4.21941 17.6569 7.3436L18.364 8.05071M18.364 8.05071H14.1213M18.364 8.05071V3.80807"
                                                                                                stroke="currentColor"
                                                                                                stroke-width="1.5"
                                                                                                stroke-linecap="round"
                                                                                                stroke-linejoin="round"
                                                                                            ></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_1276_6232">
                                                                                                <rect width="24" height="24" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>
                                                                                    Restore Task
                                                                                </a>
                                                                            </li>
                                                                        </template>
                                                                    </ul>
                                                                </template>
                                                            </Popper>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template v-if="!pagedTasks.length">
                            <div class="flex justify-center items-center sm:min-h-[300px] min-h-[400px] font-semibold text-lg h-full">No data available</div>
                        </template>
                    </div>
                </div>

                <TransitionRoot appear :show="addTaskModal" as="template">
                    <Dialog as="div" @close="addTaskModal = false" class="relative z-50">
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
                                            @click="addTaskModal = false"
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
                                            {{ 'Add Task' }}
                                        </div>
                                        <div class="p-5">
                                            <form @submit.prevent="form.post(route('boardItem.store', board.id))">
                                                <div class="mb-5">
                                                    <label for="title">Title</label>
                                                    <input id="title" type="text" placeholder="Enter Task Title" class="form-input" v-model="form.title" />
                                                </div>
                                                <div class="ltr:text-right rtl:text-left flex justify-end items-center mt-8">
                                                    <button type="button" class="btn btn-outline-danger" @click="addTaskModal = false">Cancel</button>
                                                    <button type="submit" class="btn btn-primary ltr:ml-4 rtl:mr-4">{{  'Add' }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>

                <TransitionRoot appear :show="viewTaskModal" as="template">
                    <Dialog as="div" @close="viewTaskModal = false" class="relative z-50">
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
                                            @click="viewTaskModal = false"
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
                                        <div
                                            class="flex items-center flex-wrap gap-2 text-lg font-medium bg-[#fbfbfb] dark:bg-[#121c2c] ltr:pl-5 rtl:pr-5 py-3 ltr:pr-[50px] rtl:pl-[50px]"
                                        >
                                            <div>{{ selectedTask.title }}</div>
                                            <div
                                                v-show="selectedTask.priority"
                                                class="badge rounded-3xl capitalize"
                                                :class="{
                                                    'badge-outline-primary': selectedTask.priority === 'medium',
                                                    'badge-outline-warning ': selectedTask.priority === 'low',
                                                    'badge-outline-danger ': selectedTask.priority === 'high',
                                                }"
                                            >
                                                {{ selectedTask.priority }}
                                            </div>

                                            <div
                                                v-show="selectedTask.tag"
                                                class="badge rounded-3xl capitalize"
                                                :class="{
                                                    'badge-outline-success ': selectedTask.tag === 'team',
                                                    'badge-outline-info ': selectedTask.tag === 'update',
                                                }"
                                            >
                                                {{ selectedTask.tag }}
                                            </div>
                                        </div>
                                        <div class="p-5">
                                            <div class="text-base" v-html="selectedTask.description"></div>

                                            <div class="flex justify-end items-center mt-8">
                                                <button type="button" class="btn btn-outline-danger" @click="viewTaskModal = false">Close</button>
                                            </div>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
