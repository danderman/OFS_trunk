function process_payment(member_id,basket_id,amount,batchno) {
  var t = new Date();
  var datestring = t.getFullYear() + "-" + t.getMonth() + "-" + t.getDay() + " " + t.getHours() + ":" + t.getMinutes() + ":" + t.getSeconds();
  jQuery.post("ajax/receive_payments_process.php", {
    // member_id:
    // posted_by:
    // site_id:
    // delivery_id:
    process:"receive_payment",
    member_id:member_id,
    basket_id:basket_id,
    amount:amount,
    effective_datetime:datestring,
    payment_type:"cash",
    paypal_fee:"",
    paypal_comment:"",
    memo:"",
    batch_number:batchno,
    comment:""
    },
  function(receive_payment) {
    // Returned value has first ten fixed characters indicating status
    var receive_payment_status = receive_payment.substr(0,10)
    var receive_payment_result = receive_payment.substr(10)
    if (receive_payment_status == "ACCEPT    ") {
      // Payment was recorded, so close the receive_payment form
      // Then reload the member information section
      reload_detail_line(basket_id);
      }
    else if (receive_payment_status == "ERROR     ") {
      alert("Payment for member " + member_id + " failed");
      }
    else {
      }
    });
  }


function form_auto_fill ()
{
  var error_message = "Auto-fill failed for the members and information below:\n\n";
  var is_error = false;
  var raw_data = document.getElementById("auto_fill_box").value;
  var batchno = document.getElementById("auto_fill_batchno").value;
  var lines = raw_data.split(/\r?\n/g);
  for (var i = 0; i < lines.length; i++)
  {
    if (lines[i].length > 0)
    {
      var values = lines[i].split(/ ?\t ?/);
      values[0] = values[0].replace(/[^\d]/g, "");
      values[1] = values[1].replace(/[^\d.-]/g, "");
      if (typeof(values[2]) === "string") {values[2] = values[2].replace(/[^\d.-]/g, "");}

      var basket_id = document.getElementById("member_id"+values[0]).querySelector(".basket_id").value;
      if (basket_id && (values[1] || values[2]))
      {
        process_payment(values[0], basket_id, values[1], batchno);
      }
      else if (values[0] || values[1] || values[2])
      {
        if (! values[0])
        {
          values[0] = " ??";
        }
        if (! values[1])
        {
          values[1] = "--";
        }
        if (! values[2])
        {
          values[2] = "--";
        }
        error_message = error_message+"\t#"+values[0]+"        ( "+values[1]+" / "+values[2]+" )\n";
        is_error = true;
      }
    }
  }
  if (is_error)
  {
    alert (error_message);
  }
  else
  {
    alert ("Auto-fill completed.");
  }
}
