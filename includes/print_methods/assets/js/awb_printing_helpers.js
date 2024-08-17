const curiero_save_file = (pdfUrl, filename) => saveAs(pdfUrl + '&' + Math.random().toString(36).substring(2, 10), filename);

jQuery(function ($) {
  const {
    _wpnonce,
    loading_icon
  } = curiero_ajax_helper;

  $(document.body).on('click', '#doaction, #doaction2', async function (e) {
    const select = $(this).siblings('select');
    const selectVal = select.val();
    const checked_inputs = $('table tbody .check-column input[type="checkbox"]:not([class*="hide"]):not([id*="select-all"]):checked');
    const checked_with_awb = checked_inputs.filter((i, el) => $(el).parents('tr').find('.downloadBtn').length);

    // Bulk Generate AWB
    if (selectVal.includes('generateAWB')) {
      e.preventDefault();

      const queue_length = checked_inputs.length;
      const courier = selectVal.split('_')[1];

      checked_inputs.each((i, el) => {
        const generateAwbColumn = $(el).parents('th').siblings(`td[data-colname*="${courier}"]`);
        const button = generateAwbColumn.find('button.generateBtn');
        setTimeout(() => button.trigger('click', [{ shouldReload: i === queue_length - 1 }]), i * 1000);
      });
    }

    // Bulk Send Emails
    if (selectVal === 'bulkSendEmails') {
      e.preventDefault();

      const order_ids = checked_with_awb.serializeArray();
      if (order_ids.length === 0) {
        return false;
      }

      const emailForm = new FormData();
      emailForm.append('action', 'curiero_send_awb_email');
      emailForm.append('_wpnonce', _wpnonce);
      emailForm.append('order_ids', order_ids);

      $('.wp-header-end').after(`<div class="notice notice-warning is-dismissible bulkSendEmailsNotice"><p>Emailuri de notificare cu AWB-urile generate sunt in curs de trimitere. Va rugam asteptati.</p></div>`);

      const request = await fetch(ajaxurl, {
        method: 'POST',
        body: emailForm
      });

      if (request.ok) {
        let notice = $('.bulkSendEmailsNotice').removeClass('notice-warning').addClass('notice-success').html('<p>Emailurile au fost trimise.</p>');
        setTimeout(function () { notice.fadeOut(750) }, 5000);
      } else {
        let notice = $('.bulkSendEmailsNotice').removeClass('notice-warning').addClass('notice-error').html('<p>A aparut o eroare in trimiterea email-urilor.</p>');
        setTimeout(function () { notice.fadeOut(750) }, 5000);
      }

      return false;
    }

    // Bulk Download AWB
    if (selectVal === 'bulkDownloadAWB') {
      e.preventDefault();

      checked_inputs.each((key, input) => {
        $(input).parents('tr').find('.downloadBtn').each((button_key, button_el) => {
          const button = $(button_el),
            url = button.attr('href'),
            awb_nr = button.text().trim();

          if (undefined === url || '' === url) return;
          setTimeout(curiero_save_file, key * 1000, url, awb_nr);
        })
      });

      return false;
    }
  });

  // Single Generate AWB
  $(document.body).on('click', 'button.generateBtn', async function (ev, evParams = { shouldReload: true }) {
    const el = ev.currentTarget;
    const original_img = el.firstChild.src;
    const { courier, order_id } = el.dataset;
    if (!courier || !order_id) return;

    const generateForm = new FormData();
    generateForm.append('action', 'curiero_generate_awb');
    generateForm.append('_wpnonce', _wpnonce);
    generateForm.append('courier', courier);
    generateForm.append('order_id', order_id);

    $(el).parents('tr').find('.generateBtn').prop('disabled', true);
    el.firstChild.src = loading_icon;

    const generateRequest = await fetch(ajaxurl, {
      method: 'POST',
      body: generateForm
    });

    if (generateRequest.ok) {
      const { success } = await generateRequest.json();
      if (undefined !== success && false === success) {
        alert('AWB-ul nu a putut fi generat. Incercati generarea AWB-ului din comanda.');
        $(el).parents('tr').find('.generateBtn').prop('disabled', false);
        el.firstChild.src = original_img
      }
      if (evParams.shouldReload) location.reload();
    } else {
      $('.wp-header-end').after(`<div class="notice notice-error is-dismissible"><p>${data.responseText}</p></div>`);
      $(el).parents('tr').find('.generateBtn').prop('disabled', false);
      el.firstChild.src = original_img
    }
  });
});
