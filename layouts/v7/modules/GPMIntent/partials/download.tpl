<script type="text/javascript" src="layouts/v7/lib/jquery/jquery.min.js"></script>
<link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
<link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
<script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script>
<ul style="list-style-type: none;
   		margin: 0;
   		padding: 0;
   		overflow: hidden;
   		background-color: #333;">
    <li style="float:right"><a style="display: block;
      		color: white;
      		text-align: center;
      		padding: 14px 16px;
      		text-decoration: none;
      		background-color: #bea364;" href="{$DOWNLOAD_LINK}">Download</a>
    </li>
    <li style="float: right;margin-top: 5px;margin-right: 5px;width: 198px;">
        <select class="inputElement select2" name="view_type" id="view_type">
            <option {if $smarty.request.type  eq 'full' } selected {/if} value="full">Full</option>
            <option {if $smarty.request.type  eq 'simple' } selected {/if} value="simple">Simple</option>
        </select>
    </li>
</ul>
{literal}
    <style>
        .select2-container .select2-choice>.select2-chosen {
            width: 171px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
        jQuery("body").on('change', '#view_type', function(e) {
            var element = jQuery(e.currentTarget);
            var bankId = element.val();
            window.location.replace(window.location.href.split('&type=')[0] + '&type=' + bankId);
        });
    </script>
{/literal}