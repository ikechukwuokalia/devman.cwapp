const ptchUsr = (code, server, status) => {
  let frm = $("#alter-usr-form");
  if (code && server && status && frm.length && confirm(`Do you want to change the status of this user?`)) {
    frm.find("input[name=code]").val(code);
    frm.find("input[name=server]").val(server);
    frm.find("input[name=status]").val(status);
    frm.submit();
  }
}
function lsUsr (users) {
  let conf = pConf(0);
  let html = "";
  $.each(users, function(_i, usr) {
    html += "<tr>";
    html += `<td><code>${usr.status}</code></td>`;
      html += `<td> <code onclick="clipboardCopy('${usr.code}');" class="bold color-${usr.status == "ACTIVE" ? "green" : "red"}">${usr.codeSplit}</code> </td>`;
      html += `<td><code>${usr.isSystem ? "Yes" : "No"}</code></td>`;
      html += `<td> ${usr.name} ${usr.surname}</td>`;
      html += `<td> <a href="mailto:${usr.email}">${usr.emailMask}</a></td>`;
      html += `<td> <a href="tel:${usr.phone}">${usr.phoneMask}</a></td>`;
      html += `<td>`;
      if (usr.status !== "BANNED") {
          html += `<button onclick="cwos.faderBox.url('/app/developer/post/user', {code: ${usr.code}, server: '${usr.server}', callback: 'requery'}, {exitBtn:true});" type="button" class="theme-button mini blue no-shadow"><i class="far fa-edit"></i> Edit</button>`;
          html += `<select id="usr-status" style="border-color: inherit; padding: 5px; display: inline-block; margin: 5px">`;
            html += `<option value="" selected>Status</option>`;
            $.each({ACTIVE:"Activate",SUSPENDED:"Suspend", DISABLED:"Disable",BANNED:"Ban"}, function (val, title){
              if (val !== usr.status) {
                html += `<option data-server="${usr.server}" data-code="${usr.code}" value="${val}" >${title}</option>`;
              }
            });
          html += `</select>`;
        }
      html += `</td>`;
    html += "</tr>";
  });
  $(`${conf.container}`).html(html);
}

const ptchApp = (name, server, status) => {
  let frm = $("#alter-app-form");
  if (name && server && status && frm.length && confirm(`Do you want to change the status of this app?`)) {
    frm.find("input[name=name]").val(name);
    frm.find("input[name=server]").val(server);
    frm.find("input[name=status]").val(status);
    frm.submit();
  }
}
function lsApp (apps) {
  let conf = pConf(0);
  let html = "";
  $.each(apps, function(_i, app) {
    html += "<tr>";
      html += `<td>`;
        html += `<code>${app.status}</code>`;
      html += `</td>`;
      html += `<td> <code onclick="clipboardCopy('${app.name}');" class="bold color-${app.status == "ACTIVE" ? "green" : "red"}">${app.name}</code> </td>`;
      html += `<td><code>${app.prefix}</code></td>`;
      html += `<td> ${app.title}</td>`;
      html += `<td> ${numberFormat(app.requests, 0, "", ",")}</td>`;
      html += `<td>`;
      if (app.status !== "BANNED") {
          html += `<button onclick="cwos.faderBox.url('/app/developer/post/app', {name: '${app.name}', server: '${app.server}', callback: 'requery'}, {exitBtn:true});" type="button" class="theme-button mini blue no-shadow"><i class="far fa-edit"></i> Edit</button>`;
          html += `<select id="app-status" style="border-color: inherit; padding: 5px; display: inline-block; margin: 5px">`;
            html += `<option value="" selected>Status</option>`;
            $.each({ACTIVE:"Activate",SUSPENDED:"Suspend", DISABLED:"Disable",BANNED:"Ban"}, function (val, title){
              if (val !== app.status) {
                html += `<option data-server="${app.server}" data-name="${app.name}" value="${val}" >${title}</option>`;
              }
            });
          html += `</select>`;
        }
        html += `<button onclick="clipboardCopy('${app.puKey}');" type="button" class="theme-button mini asphalt no-shadow"><i class="far fa-copy"></i> Public Key</button>`;
      html += `</td>`;
    html += "</tr>";
  });
  $(`${conf.container}`).html(html);
}
function lsHst (histories) {
  let conf = pConf(0);
  let html = "";
  $.each(histories, function(_i, hst) {
    html += "<tr>";
      html += `<td> <code onclick="clipboardCopy(${hst.id});">${numberFormat(hst.id, 0, "", ",")}</code> </td>`;
      html += `<td> <code onclick="clipboardCopy('${hst.app}');" >${hst.app}</code> </td>`;
      html += `<td><code>${hst.path}</code></td>`;
      html += `<td title="${hst.createdDate}"> ${hst.created}</td>`;
      html += `<td>`;
        html += `<button onclick='clipboardCopy("${encodeURI(hst.param)}");' type="button" class="theme-button mini asphalt no-shadow"><i class="far fa-copy"></i> Request Param</button>`;
      html += `</td>`;

    html += "</tr>";
  });
  $(`${conf.container}`).html(html);
}

///////
(function(){
  $(document).on("change","select#usr-status", function(){
    if ($(this).val().length) {
      let status = $(this).val();
      let dt = $(this).find("option:selected").data();
      ptchUsr(dt.code, dt.server,status);
    }
  });
  $(document).on("change","select#app-status", function(){
    if ($(this).val().length) {
      let status = $(this).val();
      let dt = $(this).find("option:selected").data();
      ptchApp(dt.name, dt.server,status);
    }
  });
})();