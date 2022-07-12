


// function truncate_toggle(click_elem, truncated, not_truncated, container_elem){

//     /*
//     / click_elem -> The element where the click event handler will be attached
//     / truncated -> The element in dom which is hidden and stores the truncated text
//     / not_truncated -> The element in dom which is hidden and stores the full text
//     / container_elem -> The container of the toggled text
//     */

//     var show_text = function(e) {

//         var expand = $(container_elem).hasClass('is_less');

//         if(expand) {
//             // Get the full text stored in the hidden div with id -> not_truncated
//             var full_text = $(not_truncated).html();
//             $(container_elem).html(full_text);
//             $(container_elem).toggleClass('is_less');
//         } else {
//             // Get the truncated text stored in the hidden div with id -> truncated
//             var less_text = $(truncated).html();
//             $(container_elem).html(less_text);
//             $(container_elem).toggleClass('is_less');
//         }
//         e.preventDefault();
//     }

//     $(document).on("click", click_elem, show_text);
// }

// $(document).ready(function () {

//     // Initialisations
//     act_confirm();
//     tooltip_init();
//     popover_init();
//     truncate_toggle('.more_less_btn', '#truncated', '#not_truncated', '#descr_content');
//     popover_play();
// });
