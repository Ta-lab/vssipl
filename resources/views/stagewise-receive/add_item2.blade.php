<input type="hidden" name="req_qty2" class="req_qty2" id="req_qty2" value="{{$req_qty}}">
<span class="me-auto mb-3 mt-3"><button class="btn btn-secondary text-white">STEP 2-RC Details</button></span>
<div class="col-md-12 mt-3">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive' id="">
            <thead>
            <tr>
                <th><b>RC No</b></th>
                <th><b>Part No</b></th>
                <th><b>Available Quantity In KGS</b></th>
                <th><b>Available Quantity In Nos</b></th>
                <th><b>Action</b></th>
            </tr>
            </thead>
            <tbody >
                <tr class="card">
                    <td>A</td>
                    <td>A</td>
                    <td>A</td>
                    <td><input type="number" name="value" class="original" value="200" readonly></td>
                    <td><input type="number" class="filled" readonly></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function fillCardsUpToX() {
    let remaining = parseInt($('#req_qty2').val()) || 0;

    $('.card-value').each(function() {
        const max = parseInt($(this).data('original'));
        if (remaining > 0) {
            let toFill = Math.min(max, remaining);
            $(this).val(toFill);
            remaining -= toFill;
        } else {
            $(this).val(0);
        }
    });
}

$(document).ready(function() {
    // Listen to input changes
    $('#req_qty').on('input', fillCardsUpToX);

    // Optionally trigger once on page load
    fillCardsUpToX();
});

</script>
