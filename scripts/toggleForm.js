/**
 * Fonction qui gère le changement de menu du panneau
*/
function toggleForm(formId) {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const changePassForm = document.getElementById('changePass-form');
    const panelControls = document.getElementById('panel-controls');
    const panelIcons = document.getElementById('panel-icons');

    if (!panelControls.style.display) {
        panelControls.style.display = 'flex';
    }
    if (!panelIcons.style.display) {
        panelIcons.style.display = 'flex';
    }

    loginForm.style.display = 'none';
    registerForm.style.display = 'none';
    changePassForm.style.display = 'none';
    if (formId == "login-form" && panelControls.style.display == 'flex'){
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        changePassForm.style.display = 'none';
        panelControls.style.display = 'none';
        panelIcons.style.display = 'none';
    } else if(formId == "register-form") {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        changePassForm.style.display = 'none';
        panelControls.style.display = 'none';
        panelIcons.style.display = 'none';
    } else if(formId == "changePass-form"){
        loginForm.style.display = 'none';
        registerForm.style.display = 'none';
        changePassForm.style.display = 'block';
        panelControls.style.display = 'none';
        panelIcons.style.display = 'none';
    } else {
        panelControls.style.display = 'flex';
        panelIcons.style.display = 'flex';
    }
}
