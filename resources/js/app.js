import "./bootstrap";
import $ from 'jquery';
window.jQuery = window.$ = $
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import sort from '@alpinejs/sort'
window.Livewire = Livewire 
window.Alpine = Alpine
Alpine.plugin(sort)
Livewire.start()

import Select2 from 'select2';
import Sortable from "sortablejs";
import moment from 'moment';
import intlTelInput from "intl-tel-input";
import nProgress from "nprogress";
import Toastify from 'toastify-js'
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import { jsPDF } from "jspdf";


window.intlTelInput = intlTelInput;
window.NProgress = nProgress;
window.moment = moment;
window.Toastify = Toastify;
window.Calendar = Calendar
window.dayGridPlugin = dayGridPlugin
window.timeGridPlugin = timeGridPlugin
window.listPlugin = listPlugin
window.Sortable = Sortable
window.jsPDF = jsPDF;
Select2();
const AppAssets = import.meta.glob([
    '../assets/fonts/**',
    '../assets/img/**',
    '../assets/css/**',
    '../assets/js/**',
    '../assets/plugins/**/**',
])
console.log(AppAssets);
$(document).on("click", ".deleteBtn", function () {
    let title = $(this).data("title");
    let url = $(this).data("route");
    let question = $(this).data("question");
    var id = $(this).data("id");
    if (id != "" && url != "") {
        $("#GeneralDeleteModal .input[name='id']").val(id);
        $("#GeneralDeleteModal form").attr("action", url);
        $("#GeneralDeleteModal .modal_title").html(title);
        $("#GeneralDeleteModal .modal_message").html(question);
        $("#GeneralDeleteModal").modal("show");
    }
});

// Confirm close ticket button handler
$(document).on('click', 'button.confirm-close', function(e){
    var msg = $(this).data('confirm') || '{{ __('Are you sure?') }}';
    if(!confirm(msg)){
        e.preventDefault();
    }
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).on('click', 'a[data-ajax-modal="true"], button[data-ajax-modal="true"], div[data-ajax-modal="true"], span[data-ajax-modal="true"]', function () {
    let title = $(this).data("title");
    let style = $(this).data("style");
    let size = $(this).data("size");
    let url = $(this).data('url');
    $.ajax({
        url: url,
        beforeSend: function () {
            // show loader explicitly so it's hidden/shown reliably regardless of CSS classes
            $("#loader-wrapper").show();
        },
        success: function (data) {
            // protect success handler from throwing and leaving loader visible
            try {
                console.log('[ajax-modal] success handler entered for', url);
                if (!$("#generalModalPopup").length) {
                $("body").append(
                    $(
                        `<div class="modal custom-modal ${
                            style ? style : "fade"
                        }" id="generalModalPopup" role="dialog">
                            <div class="modal-dialog modal-dialog-centered ${
                                size ? "modal-" + size : ""
                            }" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        ${
                                            title
                                                ? '<h5 class="modal-title">' +
                                                    title +
                                                    "</h5>"
                                                : ""
                                        }
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="body"></div>
                                </div>
                            </div>
                        </div>`
                    )
                );
            }
            $("#generalModalPopup .body").html(data);
            $("#generalModalPopup").modal("show");
            // hide loader explicitly (multiple methods in case CSS/class toggles interfere)
            try{
                $("#loader-wrapper").hide();
                $("#loader-wrapper").removeClass('d-block');
                $("#loader-wrapper").css('display','none');
                    console.log('[ajax-modal] loader hidden after success for', url);
                    // add invisible marker so we can inspect which handler injected the modal
                    try{ $("#generalModalPopup .body").append('<div id="ajax-modal-source" style="display:none">ajax-modal</div>'); }catch(e){}
            }catch(err){
                console.error('[ajax-modal] error hiding loader', err);
            }
            // MutationObserver fallback: if modal body is later modified, ensure loader is hidden
            try{
                const target = document.querySelector('#generalModalPopup .body');
                if(target){
                    const obs = new MutationObserver(function(){
                        try{
                            const el = document.getElementById('loader-wrapper');
                            if(el){ el.style.display = 'none'; $(el).hide(); $(el).removeClass('d-block'); }
                        }catch(e){/*ignore*/}
                    });
                    obs.observe(target, { childList: true, subtree: true });
                    // disconnect after a short while to avoid leaks
                    setTimeout(()=> obs.disconnect(), 5000);
                }
            }catch(e){console.error('[ajax-modal] observer failed', e)}
            } catch (e) {
                console.error('[ajax-modal] exception in success handler', e);
                $("#loader-wrapper").hide();
            }
            if ($(".select").length > 0) {
                $(".select").each(function () {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this.select2({
                      dropdownAutoWidth: true,
                      width: '100%',
                      dropdownParent: $this.parent()
                    });
                });
            }
            if ($(".datepicker").length > 0) {
                $(".datepicker").each(function () {
                    $(this).datetimepicker({
                        format: "YYYY-MM-DD",
                        icons: {
                            up: "fa fa-angle-up",
                            down: "fa fa-angle-down",
                            next: "fa fa-angle-right",
                            previous: "fa fa-angle-left",
                        },
                    });
                });
            }
            if ($(".datetimepicker").length > 0) {
                $(".datetimepicker").each(function () {
                    $(this).datetimepicker({
                        format: "YYYY-MM-DD H:i",
                        icons: {
                            up: "fa fa-angle-up",
                            down: "fa fa-angle-down",
                            next: "fa fa-angle-right",
                            previous: "fa fa-angle-left",
                        },
                    });
                });
            }
        },
        error: function (xhr) {
            console.debug('[ajax-modal] error handler entered for', url, xhr && xhr.status);
            // ensure loader is hidden on error as well
            $("#loader-wrapper").hide();
            console.log(xhr);
            alert("something went wrong")
        },
        complete: function() {
            // always hide loader when request cycle finishes (success/error/exception)
            try {
                $("#loader-wrapper").hide();
                console.debug('[ajax-modal] complete called for', url);
            } catch (e) {
                console.error('[ajax-modal] exception in complete handler', e);
            }
        }
    });
});

if ($(".datetimepicker").length > 0) {
    $(".datetimepicker").each(function () {
        $(this).datetimepicker({
            format: "YYYY-MM-DD H:i",
            icons: {
                up: "fa fa-angle-up",
                down: "fa fa-angle-down",
                next: "fa fa-angle-right",
                previous: "fa fa-angle-left",
            },
        });
    });
}