function send() {
    const texts = document.querySelectorAll("div textarea");
    const result = document.querySelector("textarea[readonly]");
    const level = document.getElementById("coherence").value;
    let str = "";

    texts.forEach(text => str += text.value + "@separatorphp@");

    const textEncoder = new TextEncoder();
    const utf8Bytes = textEncoder.encode(str);
    const file = new Blob([utf8Bytes], { type: 'text/plain;charset=utf-8' });
    const formData = new FormData();
    formData.append('file', file);

    formData.append('level', level);
    fetch('../php/scripts.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data =>
            result.value = data.result
        )
        .catch(error => {
            console.error('Erro:', error);
        });
}

function newTextArea() {
    const textarea = document.createElement("textarea");
    const container = document.querySelector("div");

    textarea.classList.add("optional");
    textarea.placeholder = "Insira seu texto aqui...";
    container.appendChild(textarea);
}

function removeTextArea() {
    const textarea = document.getElementsByClassName("optional");

    if (textarea.length > 0)
        textarea[textarea.length - 1].remove();
}

function range(id) {
    const coherence = document.getElementById("coherence");
    const creative = document.getElementById("creative");

    switch (id) {
        case "coherence":
            creative.value = 10 - coherence.value;
            break;
        case "creative":
            coherence.value = 10 - creative.value;
            break;
    }
}