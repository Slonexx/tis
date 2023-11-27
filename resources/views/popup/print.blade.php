<style>
    body {
        font-family: 'Times New Roman', 'Arial', sans-serif !important;
    }

</style>
{!! $html !!}
<script>

    @if(isset($message))
    let message = @json($message);
    alert(JSON.stringify(message));
    @else
    window.print()
    @endif
</script>
