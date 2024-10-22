<template>
<Head title="ورود یا عضویت" />
<flash-messages />
<div class="flex items-center justify-center p-6 min-h-screen bg-crm-800">
    <div class="w-full max-w-md">
        <!-- <logo class="block mx-auto w-full max-w-xs fill-white" height="50" /> -->
        <form class="mt-8 bg-white rounded-lg shadow-xl overflow-hidden" @submit.prevent="submit">
            <div class="px-4 md:px-8 py-12">
                <!-- <h1 class="text-center text-3xl font-bold"> ورود یا عضویت </h1> -->
                <img class="h-40 w-40 object-cover mx-auto" src="/images/otp.webp"/>
                <div class="mt-6 mx-auto w-24 border-b-2" />
                <div class="text-center text-sm leading-normal md:text-base mt-6"> لطفا کد ارسال‌ شده به شماره‌ {{phone}} را وارد نمایید. </div>
                <div class="flex justify-center text-sm mt-6 ">
                    <Link class="flex border rounded-full p-2 items-center justify-center bg-sky-200 text-sky-600 hover:bg-blue-400 hover:text-white" href="/auth/reset" method="delete">ویرایش شماره </Link>
                </div>
                <!-- <v-otp-input class="justify-center items-center mt-10" dir="ltr" ref="otpInput" input-classes="otp-input" separator="-" :num-inputs="4" :should-auto-focus="true" :is-input-num="true" :conditionalClass="['one', 'two', 'three', 'four']" @on-complete="handleOnComplete" /> -->
                <text-input v-model="form.otp" :error="form.errors.otp" class="mt-10" autofocus autocapitalize="off" />
                <div v-if="form.errors.otp" class="form-error mt-2 text-center">{{ form.errors.otp }}</div>
            </div>
            <div class="flex px-4 md:px-10 py-4 bg-gray-100 border-t border-gray-100">
                <div class="flex justify-center items-center">
                    <loading-button v-if="resendbtn" :loading="form.processing" class="btn-crm" @click="resend">ارسال مجدد کد </loading-button>
                    <div v-else>
                        ارسال مجدد کد {{Math.floor(timer / 60) }}:{{ timer - Math.floor(timer / 60) * 60 }}
                    </div>
                </div>
                <loading-button :loading="form.processing" class="btn-crm mr-auto" type="submit">تایید</loading-button>
            </div>
        </form>

    </div>
</div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
//import Logo from '@/Shared/Logo'
import TextInput from '@/Shared/TextInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import FlashMessages from '@/Shared/FlashMessages.vue'


export default {
    components: {
        Head,
        LoadingButton,
        //Logo,
        TextInput,
        FlashMessages,
        Link
    },
    props: {
        phone: [String, Number],
    },
    data() {
        return {
            form: this.$inertia.form({
                otp: null,
                phone: this.phone
            }),
            timer: 120,
            resendbtn: false,
        }
    },
    watch: {
        'form.otp': {
            handler() {
                if (this.form.otp && this.form.otp.length == 4) {
                    this.submit()
                }
            },
            deep: true,
        },
    },
    methods: {
        handleOnComplete(value) {
            this.form.otp = value;
        },
        submit() {
            this.form.post(route('auth.otp.verify'))
        },
        resend() {
            this.resendbtn = false;
            this.timer = 120;
            const data = {
                phone: this.form.phone,
            }
            this.form.post(route('auth.attempt'))
        },
        updateNow() {
            if (this.timer > 0) {
                //button disabled
                this.resendbtn = false;
                this.timer -= 1
            } else {
                //button enabled
                this.resendbtn = true;
            }
        },
    },
    created() {
        setInterval(() => {
            this.updateNow()
        }, 1000)
    },

}
</script>
