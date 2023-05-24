function send() {
    const texts = document.querySelectorAll("textarea");
    let str = "";

    texts.forEach(text => str += text.value + "@separatorphp@");

    const textEncoder = new TextEncoder();
    const utf8Bytes = textEncoder.encode(str);
    const file = new Blob([utf8Bytes], { type: 'text/plain;charset=utf-8' });
    const formData = new FormData();
    formData.append('file', file);

    formData.append('level', level = 2);
    fetch('../php/scripts.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erro:', error);
        });
}

function newTextArea() {
    const textarea = document.createElement("textarea");

    document.body.appendChild(textarea);
}
