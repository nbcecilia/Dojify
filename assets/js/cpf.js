document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="cpf"]:not([disabled])').forEach(function (input) {
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('maxlength', '14');

        function formatarCpf() {
            const posicaoOriginal = input.selectionStart ?? input.value.length;
            const digitosAntesDoCursor = input.value.slice(0, posicaoOriginal).replace(/\D/g, '').length;
            const digitos = input.value.replace(/\D/g, '').slice(0, 11);
            let cpfFormatado = digitos;

            if (digitos.length > 9) {
                cpfFormatado = digitos.slice(0, 3) + '.' + digitos.slice(3, 6) + '.' + digitos.slice(6, 9) + '-' + digitos.slice(9);
            } else if (digitos.length > 6) {
                cpfFormatado = digitos.slice(0, 3) + '.' + digitos.slice(3, 6) + '.' + digitos.slice(6);
            } else if (digitos.length > 3) {
                cpfFormatado = digitos.slice(0, 3) + '.' + digitos.slice(3);
            }

            input.value = cpfFormatado;

            let novaPosicao = 0;
            let digitosContados = 0;
            while (novaPosicao < cpfFormatado.length && digitosContados < digitosAntesDoCursor) {
                if (/\d/.test(cpfFormatado[novaPosicao])) {
                    digitosContados++;
                }
                novaPosicao++;
            }
            input.setSelectionRange(novaPosicao, novaPosicao);
        }

        input.addEventListener('input', formatarCpf);
        formatarCpf();
    });
});
