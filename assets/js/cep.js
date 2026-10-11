document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-cep-form]').forEach((form) => {
        const cepInput = form.querySelector('#cep');
        const addressInput = form.querySelector('#endereco');
        const cityInput = form.querySelector('#cidade');
        const stateInput = form.querySelector('#estado');
        const lookupButton = form.querySelector('[data-cep-lookup]');
        const status = form.querySelector('#cep-status');

        if (!cepInput || !addressInput || !cityInput || !stateInput || !lookupButton || !status) {
            return;
        }

        let currentRequest = null;

        const formatCep = (value) => {
            const digits = value.replace(/\D/g, '').slice(0, 8);
            return digits.length > 5
                ? `${digits.slice(0, 5)}-${digits.slice(5)}`
                : digits;
        };

        const buscarCep = async () => {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                status.textContent = 'Informe um CEP válido com 8 números.';
                cepInput.setCustomValidity('Informe um CEP válido com 8 números.');
                cepInput.reportValidity();
                return;
            }

            cepInput.setCustomValidity('');
            if (currentRequest) {
                currentRequest.abort();
            }
            const request = new AbortController();
            currentRequest = request;
            lookupButton.disabled = true;
            status.textContent = 'Consultando CEP...';

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                    signal: request.signal,
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) {
                    throw new Error(`Falha na consulta do CEP (HTTP ${response.status}).`);
                }

                const dados = await response.json();
                if (dados.erro) {
                    status.textContent = 'CEP não encontrado. Confira o número ou preencha o endereço manualmente.';
                    return;
                }

                addressInput.value = [dados.logradouro, dados.bairro].filter(Boolean).join(', ');
                cityInput.value = dados.localidade || '';
                stateInput.value = dados.uf || '';
                status.textContent = 'Endereço, cidade e estado preenchidos. Confira os dados antes de continuar.';
                addressInput.focus();
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                console.error('Não foi possível consultar o CEP:', error);
                status.textContent = 'Não foi possível consultar o CEP. Verifique sua conexão ou preencha os campos manualmente.';
            } finally {
                if (currentRequest === request) {
                    currentRequest = null;
                    lookupButton.disabled = false;
                }
            }
        };

        cepInput.addEventListener('input', () => {
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
                lookupButton.disabled = false;
            }
            cepInput.value = formatCep(cepInput.value);
            cepInput.setCustomValidity('');
            status.textContent = '';
        });
        cepInput.addEventListener('blur', () => {
            if (cepInput.value.replace(/\D/g, '').length === 8) {
                buscarCep();
            }
        });
        lookupButton.addEventListener('click', buscarCep);
    });
});
