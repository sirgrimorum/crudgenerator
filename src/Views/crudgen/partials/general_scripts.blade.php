@if ($js_section != "")
@push($js_section)
@endif
<script>
    if (typeof window['showLoading'] !== 'function') {
        var isLoading;
        function showLoading(){
            var $inside = $("<div/>",{
                "class" : "fa-3x",
            }).append('<i class="fa fa-spinner fa-pulse"></i>');
            $isLoading = $("<div/>", {
                "id" : "crudgen_loading_modal",
                "style" : "background-color:rgba(0,0,0,0.5);color:white;position:fixed;top:0;bottom:0;left:0;right:0;display:flex;z-index:999999;justify-content:center;align-items:center;",
            }).append($inside);
            $isLoading.appendTo('body');
        }

        function hideLoading(){
            if (typeof $isLoading !== 'undefined'){
                $isLoading.remove();
                delete $isLoading;
            }
        }
    }
</script>

@if ($js_section != "")
@endpush
@endif