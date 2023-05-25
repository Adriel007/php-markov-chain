// iframe bug solve
const iframe = document.querySelector("iframe");
iframe.src = "res/html/homepage.html?rnd" + Math.random();

// Click Event
const a = document.querySelectorAll("header nav a");
a.forEach(val => {
    val.onclick = () => {
        a.forEach(others => others.style.borderBottom = "solid 3px transparent");
        val.style.borderBottom = "solid 3px #fff";
    };
});