export function showSuccessAlert(message) {
    const successAlert = document.getElementById('success-alert');
    successAlert.innerText = message;
    successAlert.style.display = 'block';
    setTimeout(() => {
        hideSuccessAlert();
    }, 5000);
}

export function hideSuccessAlert() {
    const successAlert = document.getElementById('success-alert');
    successAlert.innerText = '';
    successAlert.style.display = 'none';
}

export function showErrorAlert(error) {
    let text = '';
    if (error?.response?.data?.message) text = error.response.data.message;
    else if (error?.response?.statusText) text = error?.response?.statusText;
    else text = error;
    const successAlert = document.getElementById('error-alert');
    successAlert.innerText = text;
    successAlert.style.display = 'block';
    setTimeout(() => {
        hideErrorAlert();
    }, 10000);
}

export function hideErrorAlert() {
    const successAlert = document.getElementById('error-alert');
    successAlert.innerText = '';
    successAlert.style.display = 'none';
}