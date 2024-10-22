<template>
    <Head title="ورود یا عضویت" />
    <flash-messages />
    <div class="flex items-center justify-center p-6 min-h-screen bg-gradient-to-b from-indigo-800 to-indigo-600">
        <div class="w-full max-w-md">
            <form class="mt-8 bg-white rounded-lg shadow-xl" @submit.prevent="login">
                <div class="px-10 py-12 relative">
                    <img class="h-40 w-40 object-cover mx-auto" src="/images/login.png" />

                    <h1 class="text-center text-3xl font-bold "> ورود </h1>
                    <div class="mt-6 mx-auto w-24 border-b-2 mb-10" />
                    <div class="text-center tw-text-base md:text-xl text-gray-400">لطفا شماره تلفن همراه خود را وارد نمایید</div>
                    <!-- <vue-tel-input dir="ltr"
                        class="mt-10 p-2 focus:shadow-none rounded shadow-none text-center" v-bind="bindProps"
                        v-model="form.phone" @country-changed="prenumber"></vue-tel-input> -->
                    <text-input v-model="form.phone" :error="form.errors.phone" class="mt-10" autofocus autocapitalize="off" />
                    
                </div>
                <div v-if="btn" class="flex px-10 py-4">
                    <loading-button :loading="form.processing" class="btn-indigo mr-auto"
                        type="submit">تایید</loading-button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import TextInput from '@/Shared/TextInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import FlashMessages from '@/Shared/FlashMessages.vue'

export default {
    components: {
        Head,
        LoadingButton,
        TextInput,
        FlashMessages
    },
    data() {
        return {
            form: this.$inertia.form({
                phone: null,
            }),
            btn: false
        }
    },
    watch: {
        'form.phone': {
            handler() {
                if (!this.btn && this.form.phone && this.form.phone.length == 11) {
                    this.login()
                }
            },
            deep: true,
        },
    },
    methods: {
        login() {
            this.form.post(route('auth.attempt'))
        },
        prenumber(e) {
            if (e.dialCode != 98) {
                this.btn = true
            }
        }
    },
}
</script>
