// script.js
$(function(){
  // Prevent non-numeric entry for phone and pincode
  $('input[name="phone"], input[name="pincode"]').on('input', function(){
    this.value = this.value.replace(/[^0-9]/g, '');
  });

  $('#regForm').on('submit', function(e){
    let allFilled = true;
    $('#regForm [required]').each(function(){
      if ($(this).val().trim() === '') {
        allFilled = false;
        $(this).css('border-color', 'red');
      } else {
        $(this).css('border-color', '#ddd');
      }
    });
    if (!allFilled) {
      alert('Please fill in all required fields.');
      e.preventDefault();
      return false;
    }

    // Additional checks
    const phone = $('input[name="phone"]').val();
    const pincode = $('input[name="pincode"]').val();
    if (phone.length !== 10) {
      alert('Phone number must be exactly 10 digits.');
      e.preventDefault();
      return false;
    }
    if (pincode.length !== 6) {
      alert('Pincode must be exactly 6 digits.');
      e.preventDefault();
      return false;
    }

    const file = $('input[name="profile_pic"]')[0].files[0];
    if (file && file.size > 2*1024*1024) {
      alert('Profile picture must be less than 2 MB.');
      e.preventDefault();
      return false;
    }

    $('#submitBtn').prop('disabled', true).text('Submitting...');
  });
});
