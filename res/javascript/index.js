// await fetch("res/php/machine-learning.php?level=5&group=love").then(res => res.json());
(function () {
    // Click Event
    const a = document.querySelectorAll("header nav a");
    a.forEach(val => {
        val.onclick = () => {
            a.forEach(others => others.style.borderBottom = "solid 3px transparent");
            val.style.borderBottom = "solid 3px #fff";
        };
    });
})();