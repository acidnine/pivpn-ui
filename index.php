<?php
require_once('auth.php');
$install_check = shell_exec("which openvpn");
if($install_check){
    $version = 'OpenVPN v';
    $version .= shell_exec("openvpn --version | awk '/OpenVPN/ {print $2}' | head -n 1");//{print $2\")\"}
}else{
    $version = 'Wireguard ';
    $version .= shell_exec("wg --version | awk '/ v/ {print $2}'");//{print $2\")\"}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PiVPN</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .pivpn-logo {
            height: 64px;
            width: 64px;
            background: url(img/pivpnlogo_64.png);
            display: inline-block;
            border-radius: 50%;
            box-sizing: border-box;
            box-shadow: 7px 7px 10px #cbced1, -7px -7px 10px white;
        }
        .table-responsive { /*make table responsive */
            overflow-x: auto;
        }
        #passwordField { display: none; }
        #qr_text {
            isolation: isolate;
            mix-blend-mode: difference;
            /*background-color: #000;
            color: #fff;*/
            font-family: "Courier New", Courier, monospace;
            line-height: 16px;
            letter-spacing: -1px;
            width: 61ch;
            resize: none;
            overflow: hidden;
            padding: 0px;
            border: 0;
        }
        @media only screen and (max-width: 994px) {
          #qr_text {
            font-size: 0.65rem;
            line-height: 0.65rem;
            letter-spacing: -1px;
            width: 57ch;
          }
          #clientList tr td {
            background-color: #cff4fc;
          }
          #clientList tr.table-sm-danger td {
            background-color: #f8d7da;
          }
        }
        /* allow tooltip on disabled buttons */
        .btn:disabled {
            pointer-events: auto;
            cursor: not-allowed;
        }
        #qr_gen > img {
            width: 100%;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container pt-4 min-vh-100 d-flex flex-column">
        <div>
            <h1><div class="pivpn-logo align-middle mb-2"></div> PiVPN Status</h1>
        </div>
        <div class="d-flex mb-3">
            <div class="flex-fill text-start"><?=$version?></div>
            <div class="flex-fill text-end"><span id="username"><?=$_SESSION['LOGIN']?></span></div>
        </div>
        <div class="d-flex mb-3">
            <span class="flex-fill text-start">
                <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal" data-bs-target="#addClientModal">New Client <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg></button>
            </span>
            <span class="flex-fill text-center">
                <button type="button" class="btn btn-info mx-1" id="refresh">Refresh <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bootstrap-reboot" viewBox="0 0 16 16"><path d="M1.161 8a6.84 6.84 0 1 0 6.842-6.84.58.58 0 1 1 0-1.16 8 8 0 1 1-6.556 3.412l-.663-.577a.58.58 0 0 1 .227-.997l2.52-.69a.58.58 0 0 1 .728.633l-.332 2.592a.58.58 0 0 1-.956.364l-.643-.56A6.8 6.8 0 0 0 1.16 8z"/><path d="M6.641 11.671V8.843h1.57l1.498 2.828h1.314L9.377 8.665c.897-.3 1.427-1.106 1.427-2.1 0-1.37-.943-2.246-2.456-2.246H5.5v7.352zm0-3.75V5.277h1.57c.881 0 1.416.499 1.416 1.32 0 .84-.504 1.324-1.386 1.324z"/></svg></button>
            </span>
            <span class="flex-fill text-center">
                <button type="button" class="btn btn-info mx-1" id="users">Local <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg></button>
            </span>
            <span class="flex-fill text-end">
                <a href="logout.php" type="button" class="btn btn-danger ms-1">Logout <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-closed" viewBox="0 0 16 16"><path d="M3 2a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v13h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3zm1 13h8V2H4z"/><path d="M9 9a1 1 0 1 0 2 0 1 1 0 0 0-2 0"/></svg></a>
            </span>
        </div>
        <div class="table-responsive flex-fill">
            <table class="table table-hover" id="clients">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="d-none d-sm-table-cell">Seen</th>
                        <th>Data</th>
                        <th class="d-none d-sm-table-cell">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="clientList" class="table-group-divider"></tbody>
            </table>
        </div>
        <footer class="align-self-end w-100 d-flex flex-wrap justify-content-between align-items-center py-2 mt-1 border-top">
            <p class="col-md-4 mb-0 text-body-secondary">&#8734; / pivpn-ui</p>
            <a href="/" class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none"><svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg></a>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="" class="nav-link px-2 text-body-secondary" id="reload">Reload</a></li>
                <li class="nav-item"><a href="https://github.com/acidnine/pivpn-ui" target="_blank" class="nav-link px-2 text-body-secondary">Github</a></li>
            </ul>
        </footer>
    </div>
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">New <?php if($install_check){ ?>OpenVPN<?php }else{ ?>Wireguard<?php } ?> Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addClientForm">
                        <div class="mb-3">
                            <label for="clientName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="clientName" pattern="[a-zA-Z0-9.@_\-]{1,}">
                        </div>
                        <?php if($install_check){ // only for openvpn ?>
                        <div class="mb-3">
                            <label for="clientDays" class="form-label">Days</label>
                            <input type="number" class="form-control" id="clientDays" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="requirePassword">
                            <label class="form-check-label" for="requirePassword">Require Password</label>
                        </div>
                        <div class="mb-3" id="passwordField">
                            <label for="clientPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="clientPassword">
                        </div>
                        <?php } ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="addClientForm" class="btn btn-primary" id="saveClient">Create Client</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientModalLabel" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewClientModalLabel">View Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="viewClientForm">
                        <div class="mb-3">
                            <label for="viewClientName" class="form-label">Name</label>
                            <input type="text" class="form-control border-0 fw-bold" id="viewClientName" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="viewClientStatus" class="form-label">Status</label>
                            <input type="text" class="form-control border-0 fw-bold" id="viewClientStatus" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="viewClientSeen" class="form-label">Last Seen</label>
                            <input type="text" class="form-control border-0" id="viewClientSeen" readonly>
                            <input type="text" class="form-control border-0 fw-bold" id="viewClientSeenAgo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="viewClientRemoteIP" class="form-label">Remote IP</label>
                            <input type="text" class="form-control border-0" id="viewClientRemoteIP" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="viewClientVirtualIP" class="form-label">Virtual IP</label>
                            <input type="text" class="form-control border-0" id="viewClientVirtualIP" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="viewClientCreated" class="form-label">Created</label>
                            <input type="text" class="form-control border-0 fw-bold" id="viewClientCreated" readonly>
                            <input type="text" class="form-control border-0" id="viewClientCreatedAgo" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="" class="btn btn-success" id="download" download>File <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-download" viewBox="0 0 16 16"><path d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/><path d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z"/></svg></a>
                    <button class="btn btn-success mx-0" id="copy">Copy <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></button>
                    <button type="button" class="btn btn-success me-0" id="view_qr">QR <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/><path d="M12 9h2V8h-2z"/></svg></button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="qrClientModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="qrClientModalLabel" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrClientModalLabel">QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="qrClientName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="qrClientName" readonly>
                    </div>
                    <div id="qr_gen" class="m-sm-5 mb-5"></div>
                    <button type="button" class="btn btn-success" id="qr_show_alt">Show CLI/Text QR <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/><path d="M12 9h2V8h-2z"/></svg></button>
                    <div id="qr_alt" class="d-none">
                        <textarea id="qr_text" class="text-nowrap font-monospace text-left" rows="35"></textarea>
                    </div>
                    <!--<div id="qr" class="text-nowrap text-center"></div>-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="usersModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="usersModalLabel" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usersModalLabel">Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless" id="localclients">
                            <thead>
                                <tr>
                                    <th>Login</th>
                                    <th>Password</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersList"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="sqlite_dump.php?limit=200" type="button" class="btn btn-secondary" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bug" viewBox="0 0 16 16"><path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623M4 7v4a4 4 0 0 0 3.5 3.97V7zm4.5 0v7.97A4 4 0 0 0 12 11V7zM12 6a4 4 0 0 0-1.334-2.982A3.98 3.98 0 0 0 8 2a3.98 3.98 0 0 0-2.667 1.018A4 4 0 0 0 4 6z"/></svg></a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/moment-with-locales.js"></script>
    <script type="text/javascript" src="js/qrcode.min.js"></script>
    <script>
        $(function(){
            $('[data-toggle="tooltip"]').tooltip();// enable bootstrap tooltips
            <?php if($install_check){ // only for openvpn ?>
            $("#requirePassword").change(function(){
                $("#passwordField").toggle(this.checked);
            });
            <?php } ?>
            
            //$("#saveClient").on("click",function(){$("#addClientForm").trigger('submit');});
            
            $("#addClientForm").submit(function(event){
                event.preventDefault(); // Prevent form submission
                const name = $("#clientName").val();
                if(!checkName(name)){ return false; }
                <?php if($install_check){ // only for openvpn ?>
                const days = $("#clientDays").val();
                const password = passwordRequired ? $("#clientPassword").val() : "";
                const passwordRequired = $("#requirePassword").is(":checked");
                const data = {'cmd':'new','name':name,'days':days,'password_req':passwordRequired,'password':password};
                <?php }else{ ?>
                const data = {'cmd':'new','name':name};
                <?php } ?>
                $.ajax({url:'set-client.php',type:'POST',data:data}).done(function(data){
                    if(data.result == false){
                        alert('There was a problem creating the client.');
                        console.log(data);
                    }
                });
                getClient(name);
                $("#addClientModal").modal('hide');
                $("#addClientForm")[0].reset(); // Clear the form
                $("#passwordField").hide(); //hide password field
            });
            
            function checkName(clientName){
                if(!clientName.match(/[a-zA-Z0-9.@_-]/)){
                    alert("Name can only contain alphanumeric characters and these symbols (.-@_).");
                }else if(clientName.match(/^[0-9]+$/)){
                    alert("Names cannot be integers.");
                }else if(clientName.match(/\s|'/)){
                    alert("Names cannot contain spaces.");
                }else if(clientName.startsWith("-")){
                    alert("Name cannot start with - (dash).");
                }else if(clientName.startsWith(".")){
                    alert("Names cannot start with a . (dot).");
                }else if(clientName.length === 0){
                    alert("You cannot leave the name blank.");
                }else{
                    return true;
                }
                return false;
            }
            
            function getDetailedFromNow(date) {
                const duration = moment.duration(moment().utc().diff(date));
                const years = duration.years();
                const months = duration.months();
                const days = duration.days();
                const hours = duration.hours();
                const minutes = duration.minutes();
                const seconds = duration.seconds();
                let result = '';
                if (years > 0) {result += `${years} Y${years > 1 ? '' : ''} `;}
                if (months > 0) {result += `${months} M${months > 1 ? '' : ''} `;}
                if (days > 0) {result += `${days} d${days > 1 ? '' : ''} `;}
                if (hours > 0) {result += `${hours} h${hours > 1 ? '' : ''} `;}
                if (minutes > 0) {result += `${minutes} m${minutes > 1 ? '' : ''} `;}
                if (seconds > 0) {result += `${seconds} s${seconds > 1 ? '' : ''} `;}
                return result.trim() + ' ago';
            }
            
            // https://momentjs.com/docs/#/displaying/format/
            function getTimeSince(type,dateString){
                if(type == 'seen'){
                    const format = "MMM DD YYYY - HH:mm:ss";
                    const date = moment.utc(dateString, format);
                    if(date.toDate() != 'Invalid Date'){
                        return getDetailedFromNow(date);
                    }else{
                        return '(not yet)';
                    }
                }
                if(type == 'created'){
                    const format = "DD MMM YYYY, HH:mm";
                    const date = moment.utc(dateString, format);
                    const dateUTC = moment().utc();
                    const duration = moment.duration(dateUTC.diff(dateUTC));
                    return getDetailedFromNow(date);
                }
                if(type == 'lastlogin'){
                    const format = "YYYY-MM-DD HH:mm:ss";
                    const date = moment.utc(dateString, format);
                    const dateUTC = moment().utc();
                    const duration = moment.duration(dateUTC.diff(dateUTC));
                    return getDetailedFromNow(date);
                }
            }
            
            function addClientToTable(client){
                var newRow = '<tr class="align-middle '+((client.status!='Enabled')?'table-sm-danger':'table-sm-info')+'" data-name="'+client.name+'">';
                var arrow_up = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5"/></svg>';
                var arrow_down = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4"/></svg>';
                var arrow_lr = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5m14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5"/></svg>';
                newRow += '<td class="text-break">'+client.name+'</td><td class="text-nowrap d-none d-sm-table-cell">'+client.seen+'<br><sub>'+client.seen_ago+'</sub></td><td class="text-nowrap font-monospace"><sub>In:&nbsp;'+arrow_down+client.bytes_in+'</sub><br><sub>Out:'+arrow_up+client.bytes_out+'</sub></td><td class="d-none d-sm-table-cell">'+client.status+'</td>';
                newRow += '<td>';
                // THIS IS HOW WE PASS THE DATA TO THE MODAL... :/
                newRow += '<div class="d-flex align-items-center justify-content-evenly flex-wrap" data-name="'+client.name+'" data-seen="'+client.seen+'" data-seen_ago="'+client.seen_ago+'" data-created="'+client.created+'" data-created_ago="'+client.created_ago+'" data-status="'+client.status+'" data-remote_ip="'+client.remote_ip+'" data-virtual_ip="'+client.virtual_ip+'">';
                newRow += '<button class="btn btn-sm btn-primary px-sm-4 py-sm-2" data-cmd="view">View</button>';
                if(client.status == 'Enabled'){
                    newRow += '<button class="btn btn-sm btn-warning px-sm-4 py-sm-2 my-1" data-cmd="disable">Disable</button>';
                }else{
                    newRow += '<button class="btn btn-sm btn-success px-sm-4 py-sm-2 my-1" data-cmd="enable">Enable</button>';
                }
                if(client.status == 'Disabled'){
                    newRow += '<button class="btn btn-sm btn-danger py-sm-2" data-cmd="delete">Delete</button>';
                }else{
                    newRow += '<button class="btn btn-sm btn-danger py-sm-2" data-cmd="delete" data-toggle="tooltip" data-placement="top" title="Disable client before deleting." disabled>Delete</button>';
                }
                newRow += '</div>';
                newRow += '</td>';
                newRow += '</tr>';
                $("#clientList").append(newRow);
            }
            
            function getTextQR(name){
                $.ajax({url:'get-clients.php',type:'POST',data:{'cmd':'qr','name':name}}).done(function(data){
                    $('#qrClientName').val(name);
                    $('#qr_text').html(data.qr);
                });
            }
            function getClient(name){
                $.ajax({url:'get-clients.php',type:'POST',data:{'cmd':'get','name':name}}).done(function(data){
                    console.log('get-clients.php',data);
                    var status = 'Enabled';
                    if(data.client.status == false){
                        var status = 'Disabled';
                    }
                    var client = {'name':name,'pubkey':data.client.pub_key,'seen':data.client.seen,'seen_ago':getTimeSince('seen',data.client.seen),'created':data.client.created,'created_ago':getTimeSince('created',data.client.created),'bytes_in':data.client.bytes_in,'bytes_out':data.client.bytes_out,'remote_ip':data.client.remote_ip,'virtual_ip':data.client.virtual_ip,'status':status};
                    addClientToTable(client);
                });
            }
            function getClients(){
                $.ajax({url:'get-clients.php',type:'GET'}).done(function(data){
                    if(Object.keys(data.enabled).length > 0){
                        for(const key in data.enabled){
                            var client = {'name':key,'pubkey':data.enabled[key].pub_key,'seen':data.enabled[key].seen,'seen_ago':getTimeSince('seen',data.enabled[key].seen),'created':data.enabled[key].created,'created_ago':getTimeSince('created',data.enabled[key].created),'bytes_in':data.enabled[key].bytes_in,'bytes_out':data.enabled[key].bytes_out,'remote_ip':data.enabled[key].remote_ip,'virtual_ip':data.enabled[key].virtual_ip,'status':'Enabled'};
                            addClientToTable(client);
                        }
                    }
                    if(Object.keys(data.disabled).length > 0){
                        for(const key in data.disabled){
                            var client = {'name':key,'pubkey':data.disabled[key].pub_key,'seen':'(disabled)','seen_ago':'(disabled)','created':data.disabled[key].created,'created_ago':getTimeSince('created',data.disabled[key].created),'bytes_in':'0B','bytes_out':'0B','remote_ip':'(disabled)','virtual_ip':'(disabled)','status':'Disabled'};
                            addClientToTable(client);
                        }
                    }
                });
            }
            
            // UPDATE THE VPN CLIENT
            function setClient(cmd,name){
                if(confirm("Are you sure you want to "+cmd+"? "+name)){
                    $.ajax({url:'set-client.php',type:'POST',data:{'cmd':cmd,'name':name}}).done(function(data){
                        if(data.result == true){
                            if(cmd == 'enable'){
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').text("Disable");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').removeClass("btn-success");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').addClass("btn-warning");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').data("cmd","disable");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(3)').attr("disabled",true);
                                $('#clientList tr[data-name="'+name+'"]').removeClass("table-sm-danger");
                                $('#clientList tr[data-name="'+name+'"]').addClass("table-sm-info");
                                $('#clientList tr[data-name="'+name+'"] td:nth-child(4)').text("Enabled");
                            }
                            if(cmd == 'disable'){
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').text("Enable");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').removeClass("btn-warning");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').addClass("btn-success");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(2)').data("cmd","enable");
                                $('#clientList > tr > td > div[data-name="'+name+'"] > button:nth-child(3)').attr("disabled",false);
                                $('#clientList tr[data-name="'+name+'"]').removeClass("table-sm-info");
                                $('#clientList tr[data-name="'+name+'"]').addClass("table-sm-danger");
                                $('#clientList tr[data-name="'+name+'"] td:nth-child(4)').text("Disabled");
                            }
                            if(cmd == 'delete'){
                                $('#clientList > tr > td > div[data-name="'+name+'"]').parent().parent().remove();
                            }
                        }else{
                            alert('Something went wrong when trying to '+cmd+' the client '+name+'.');
                        }
                    });
                }
            }
            // OPEN LOCAL USERS MODAL
            $('#users').on('click',function(){
                $.ajax({url:'get-users.php',type:'GET'}).done(function(data){
                    $('#usersList').empty();
                    for(const key in data){
                        //console.log(key,data[key]);
                        var newrow = '<tr class="align-middle">';
                        newrow += '<td><div class="input-group flex-nowrap"><input type="text" class="form-control input-sm" value="'+data[key].login+'" pattern="[a-zA-Z0-9.@_\\-]{1,}"><button class="btn btn-sm btn-success updateuser" data-userid="'+key+'" data-user="'+data[key].login+'" data-cmd="username"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/><path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/></svg></button></div></td>';
                        newrow += '<td><div class="input-group flex-nowrap"><input type="password" class="form-control input-sm" value="" placeholder="Change Password"><button class="btn btn-sm btn-success updateuser" data-userid="'+key+'" data-user="'+data[key].login+'" data-cmd="password"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/><path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/></svg></button></div></td>';
                        newrow += '<td>';
                        newrow += '<div class="d-flex align-items-center justify-content-evenly flex-wrap" data-userid="'+data[key].id+'">';
                        if(key == 1){var disable_del = true;}else{var disable_del = false;}
                        newrow += '<button class="btn btn-sm btn-danger px-sm-4 py-2"'+(disable_del?'disabled':'')+'><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg></button>';
                        newrow += '</div>';
                        newrow += '</td></tr>';
                        newrow += '<tr class="border-bottom"><td colspan="3"><sup>Last Login: '+data[key].lastlogin+' UTC - '+getTimeSince('lastlogin',data[key].lastlogin)+'</sup></td></tr>';
                        $('#usersList').append(newrow);
                    }
                    $('#usersModal').modal('show');
                });
            });
            // - UPDATE USER INFO
            $(document).on('click','.updateuser',function(){
                var cmd = $(this).data('cmd');
                var id = $(this).data('userid');
                var val = $(this).prev('input').val();
                var data = {'cmd':cmd,'id':id,'val':val};
                if(cmd == 'password' && val == $(this).data('user')){alert('~~Password shouldn\'t be your username..!~~');return false;}
                if(cmd == 'username' && val == $(this).data('user')){return false;}// likely clicking on it without changing it
                if(cmd == 'password' && val.length <= 0){return false;}// don't allow empty values
                if(cmd == 'username' && val.length <= 0){alert('Username shouldn\'t be empty.');return false;}// don't allow empty values
                if(cmd == 'password' && val.length < 8){alert('~~Password is too short!~~');return false;}
                $.ajax({url:'set-user.php',type:'POST',data:data}).done(function(data){
                    if(data.result == false){
                        alert(data.message);
                    }else{
                        if(cmd == 'username'){
                            $('#username').text(val);
                            $('button[data-userid="'+id+'"]').data('user',val);
                        }
                    }
                });
            });
            
            // COPY TO CLIPBOARD
            //   REQUIRES SSL
            //async function copyDownloadedText(text) {
            //    if (navigator.clipboard && navigator.clipboard.writeText) {
            //        navigator.clipboard.writeText(text)
            //            .then(() => {
            //                console.log('Text copied to clipboard');
            //            })
            //            .catch(err => {
            //                console.error('Failed to copy: ', err);
            //            });
            //        //try {
            //        //    await navigator.clipboard.writeText(text);
            //        //    console.log('Text copied to clipboard');
            //        //} catch (err) {
            //        //    console.error('Failed to copy: ', err);
            //        //}
            //    }else{
            //        console.log('Clipboard API not supported');
            //    }
            //}
            // COPY TO CLIPBOARD - WORKAROUND
            function unsecuredCopyToClipboard(text) {
                const bodyAttach = document.getElementById('viewClientForm');// DIDNT WORK WITH document.body. .. MODAL ISSUE?
                const textArea = document.createElement("textarea");
                textArea.value = text;
                bodyAttach.appendChild(textArea);
                textArea.focus();
                textArea.select();
                textArea.setSelectionRange(0, 99999);
                try{
                    document.execCommand('copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(copyText.value);// REQUIRES SSL
                    }
                }catch(err){
                    console.error('Unable to copy to clipboard', err);
                }
                bodyAttach.removeChild(textArea);
            }
            $('#copy').on('click',function(){
                var name = $('#viewClientName').val();
                $.ajax({url:'get-file.php',type:'POST',data:{'file':name}}).done(function(data){
                    unsecuredCopyToClipboard(data.qr);
                });
                // REQUIRES SSL
                //var data = new FormData();
                //data.append('file',name);
                //fetch('get-file.php',{method:'POST',headers:{'Accept':'application/json'},body:data}).then(response => response.text()).then(text=>copyDownloadedText(text));
                //fetch('get-file.php',{method:'POST',headers:{'Accept':'application/json'},body:data}).then(response => response.text()).then(text=>unsecuredCopyToClipboard(text));
            });
            
            // SHOW JS GENERATED IMAGE QR CODE
            $('#view_qr').on('click',function(){
                var name = $('#viewClientName').val();
                // PREP IMAGE QR
                $('#qr_gen').attr('title','');
                $('#qr_gen').empty();
                $.ajax({url:'get-file.php',type:'POST',data:{'file':name}}).done(function(data){
                    new QRCode(document.getElementById("qr_gen"),{
                        text: data.qr,
                        width: 512,
                        height: 512,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        //correctLevel : QRCode.CorrectLevel.L // allows up to 7% damage
                        correctLevel : QRCode.CorrectLevel.M   // allows up to 15% damage
                        //correctLevel : QRCode.CorrectLevel.Q // allows up to 25% damage
                        //correctLevel : QRCode.CorrectLevel.H // allows up to 30% damage
                    });
                });
                if($('#qr_alt').hasClass('d-block')){
                    $('#qr_alt').removeClass('d-block');
                    $('#qr_alt').addClass('d-none');
                    $('#qr_show_alt').html('Show CLI/Text QR <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/><path d="M12 9h2V8h-2z"/></svg>');
                }
                $("#qrClientModal").modal('show');
            });
            // SHOW TEXT/CLI QR CODE
            $(document).on('click','#qr_show_alt',function(){
                if($('#qr_alt').hasClass('d-none')){
                    // GRAB CLI/TEXT QR
                    var name = $('#viewClientName').val();
                    getTextQR(name);
                    $('#qr_alt').removeClass('d-none');
                    $('#qr_alt').addClass('d-block');
                    $(this).html('Hide CLI/Text QR <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/><path d="M12 9h2V8h-2z"/></svg>');
                }else{
                    $('#qr_alt').removeClass('d-block');
                    $('#qr_alt').addClass('d-none');
                    $(this).html('Show CLI/Text QR <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/><path d="M12 9h2V8h-2z"/></svg>');
                }
            });
            
            // PAGE REFRESH
            $('#refresh').on('click',function(){
                $("#clientList").empty();
                getClients();
            });
            // PAGE RELOAD
            $('#reload').on('click',function(){
                location.reload(true);
                return false;
            });
            
            // ACTION BUTTONS FOR EACH VPN CLIENT
            $(document).on('click','#clientList > tr > td > div > button',function(e){
                var name = $(this).parent().data('name');
                var cmd = $(this).data('cmd');
                if(cmd == 'view'){
                    // PREP DOWNLOAD CONFIG LINK
                    $('#download').attr("href", "get-file.php?file="+name);
                    // PREP VALUES
                    $('#viewClientName').val(name);
                    $('#viewClientStatus').val($(this).parent().data('status'));
                    $('#viewClientSeen').val($(this).parent().data('seen'));
                    $('#viewClientSeenAgo').val($(this).parent().data('seen_ago'));
                    $('#viewClientCreated').val($(this).parent().data('created'));
                    $('#viewClientCreatedAgo').val($(this).parent().data('created_ago'));
                    $('#viewClientRemoteIP').val($(this).parent().data('remote_ip'));
                    $('#viewClientVirtualIP').val($(this).parent().data('virtual_ip'));
                    $('#viewClientModal').modal('show');
                }else if(cmd == 'enable' || cmd == 'disable' || cmd == 'delete'){
                    setClient(cmd,name);
                }
            });
            
            // LOAD ALL CLIENTS, PAGE IS NOT LIVE!
            getClients();
        });
    </script>
</body>
</html>
