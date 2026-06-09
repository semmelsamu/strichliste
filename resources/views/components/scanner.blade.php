<div
    x-data="{
        init() {
            scanner((barcode) => {
                this.$refs.barcode.value = barcode;
                this.$refs.form.submit();
            });
        },
    }"
>
    {{ $slot }}
</div>
