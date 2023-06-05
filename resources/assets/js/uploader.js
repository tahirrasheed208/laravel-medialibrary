"use strict";

function uploadFile(element) {
    element.parentElement.querySelector('input[type=file]').click();
}

function changeFile(element) {
    element.closest('.lm-file-upload-box').querySelector('input[type=file]').click();
}

function chooseFile(element) {
    readFile(element);
    element.parentElement.querySelector('.lm-upload-button').classList.add('d-none');
    element.parentElement.querySelector('.lm-remove-button').classList.remove('d-none');

    setRemoveFieldValue(element);
}

function removeFile(element) {
    element.parentElement.querySelector('input[type=file]').value = '';
    element.parentElement.querySelector('.lm-img-preview').classList.add('d-none');
    element.classList.add('d-none');
    element.parentElement.querySelector('.lm-upload-button').classList.remove('d-none');

    setRemoveFieldValue(element);
}

function readFile(input) {
    if (!input.files || input.files.length == 0) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        let target = input.parentElement.querySelector('.lm-img-preview');
        target.classList.remove('d-none');
        target.querySelector('img').src = e.target.result;
    };

    reader.readAsDataURL(input.files[0]);
}

function setRemoveFieldValue(element) {
    let remove_field = element.parentElement.querySelector('input[type=hidden]');

    if (remove_field) {
        remove_field.value = 'yes';
    }
}