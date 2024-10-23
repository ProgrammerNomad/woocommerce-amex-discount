jQuery(document).ready(function($) {

    // Check if the APS Credit Card payment method is selected
    if ($('.payment_method_aps_cc').length > 0) { 

        // Add an event listener to the card number field using event delegation
        $(document).on('input', '.aps_card_number', function() { 
            var cardNumber = $(this).val();

            // Check if the card number starts with "34" or "37" and is 15 digits long
            if ((cardNumber.startsWith("34") || cardNumber.startsWith("37")) && cardNumber.length === 15) {

                // Send an AJAX request to the server
                $.ajax({
                    url: amex_discount_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_amex_card',
                        card_number: cardNumber,
                        // Add any necessary security nonce here
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        // Update the order review with the discount
                        if (data.discount > 0) {
                            // (Implementation to update the order review section)
                            // You might need to target specific elements in your theme's order review
                            // Example: $('.order-total .amount').text( ... ); 

                            // Check if the message and button already exist
                            if ($('.payment_method_aps_cc .change-card-button').length === 0) { 
                                // Display a message with the "Change Card" button
                                $('.payment_method_aps_cc').append('<p>An American Express discount has been applied. <button type="button" class="change-card-button">Change Card</button></p>');
                            }

                            // Disable card input fields (expiry and cvc) AFTER AJAX confirmation
                            $('.aps-expiry, .aps-cvc').prop('disabled', true); 

                            // Add an event listener to the "Change Card" button
                            $(document).on('click', '.change-card-button', function() {
                                // Remove the discount using AJAX
                                $.ajax({
                                    url: amex_discount_params.ajax_url,
                                    type: 'POST',
                                    data: {
                                        action: 'remove_amex_discount', // This action is no longer needed in your PHP
                                        // You might need to add a nonce here
                                    },
                                    success: function(response) {
                                        // Update the order review to remove the discount
                                        // ... (Implementation to update the order review) ...

                                        // Re-enable and SHOW card input fields
                                        $('.aps_card_number, .aps-expiry, .aps-cvc').prop('disabled', false).show(); 

                                        // Remove the discount message and the "Change Card" button
                                        $('.payment_method_aps_cc p').remove(); 
                                    },
                                    error: function(error) {
                                        console.log(error);
                                    }
                                });
                            });

                            // Trigger WooCommerce's update_checkout event to refresh the checkout totals
                            $(document.body).trigger('update_checkout'); 

                        } else {
                            // (Implementation to remove the discount if not applied)
                            // Example: (If you added a discount line item, remove it here)

                            // Re-enable and SHOW card input fields (if they were disabled)
                            $('.aps-expiry, .aps-cvc').prop('disabled', false).show(); 

                            // Remove the discount message and "Change Card" button (if it exists)
                            $('.payment_method_aps_cc p').remove(); 

                            // Trigger update_checkout to refresh totals
                            $(document.body).trigger('update_checkout'); 
                        }
                    },
                    error: function(error) {
                        console.log(error); 
                    }
                });

            } else {
                // If it's not a complete Amex card, remove the discount and re-enable fields
                // (Implementation to remove the discount if not applied)
                // Example: (If you added a discount line item, remove it here)

                // Re-enable and SHOW card input fields (if they were disabled)
               // $('.aps_card_number, .aps-expiry, .aps-cvc').prop('disabled', false).show(); 

                // Remove the discount message and "Change Card" button (if it exists)
               // $('.payment_method_aps_cc p').remove(); 

                // Trigger update_checkout to refresh totals
                //$(document.body).trigger('update_checkout'); 
            } 
        });

    } 

});