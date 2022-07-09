let targets = document.querySelectorAll('.trapezoid_right');//アニメーションさせたい要素
let offset = 100;//アニメーションタイミング

window.addEventListener('scroll', function () {//スクロールしたとき

    var scroll = window.scrollY;//スクロール量を取得
    var h = window.innerHeight;//画面の高さを取得

    for (let target of targets) {
        var pos = target.getBoundingClientRect().top + scroll;//アニメーションさせたい要素の位置を取得
        if (scroll > pos - h + offset) {//スクロール量 > アニメーションさせたい要素の位置
            target.classList.add('is-animated');
        }
    }

});