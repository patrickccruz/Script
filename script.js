// Função para salvar os dados do formulário no localStorage
function salvarDadosFormulario() {
    const formData = {
      dataChamado: document.getElementById("dataChamado").value,
      numeroChamado: document.getElementById("numeroChamado").value,
      cliente: document.getElementById("cliente").value,
      nomeInformante: document.getElementById("nomeInformante").value,
      quantidadePatrimonios: document.getElementById("quantidadePatrimonios").value,
      kmInicial: document.getElementById("kmInicial").value,
      kmFinal: document.getElementById("kmFinal").value,
      horaChegada: document.getElementById("horaChegada").value,
      horaSaida: document.getElementById("horaSaida").value,
      enderecoPartida: document.getElementById("enderecoPartida").value,
      enderecoChegada: document.getElementById("enderecoChegada").value,
      informacoesAdicionais: document.getElementById("informacoesAdicionais").value,
    };
    localStorage.setItem("formData", JSON.stringify(formData));
  }
  
  // Função para carregar os dados do formulário do localStorage
  function carregarDadosFormulario() {
    const formData = JSON.parse(localStorage.getItem("formData"));
    if (formData) {
      document.getElementById("dataChamado").value = formData.dataChamado || "";
      document.getElementById("numeroChamado").value = formData.numeroChamado || "";
      document.getElementById("cliente").value = formData.cliente || "";
      document.getElementById("nomeInformante").value = formData.nomeInformante || "";
      document.getElementById("quantidadePatrimonios").value = formData.quantidadePatrimonios || "";
      document.getElementById("kmInicial").value = formData.kmInicial || "";
      document.getElementById("kmFinal").value = formData.kmFinal || "";
      document.getElementById("horaChegada").value = formData.horaChegada || "";
      document.getElementById("horaSaida").value = formData.horaSaida || "";
      document.getElementById("enderecoPartida").value = formData.enderecoPartida || "";
      document.getElementById("enderecoChegada").value = formData.enderecoChegada || "";
      document.getElementById("informacoesAdicionais").value = formData.informacoesAdicionais || "";
    }
  }
  
  // Função para coletar as informações gerais e atualizar os elementos no modal
  function infoGeral() {
    console.log("Coletando informações gerais...");
    salvarDadosFormulario();
  
    const dataChamado = document.getElementById("dataChamado").value;
    const numeroChamado = document.getElementById("numeroChamado").value;
    const cliente = document.getElementById("cliente").value;
    const nomeInformante = document.getElementById("nomeInformante").value;
    const quantidadePatrimonios = document.getElementById("quantidadePatrimonios").value;
    const kmInicial = document.getElementById("kmInicial").value;
    const kmFinal = document.getElementById("kmFinal").value;
    const horaChegada = document.getElementById("horaChegada").value;
    const horaSaida = document.getElementById("horaSaida").value;
    const enderecoPartida = document.getElementById("enderecoPartida").value;
    const enderecoChegada = document.getElementById("enderecoChegada").value;
    const informacoesAdicionais = document.getElementById("informacoesAdicionais").value;
  
    // Atualizar os parágrafos do modal
    document.getElementById("geralResp0").textContent = `Data do chamado: ${dataChamado}\nNúmero do chamado: ${numeroChamado}`;
    document.getElementById("geralResp1").textContent = `Cliente: ${cliente}\nNome de quem informou o chamado: ${nomeInformante}\nQuantidade de patrimônios tratados: ${quantidadePatrimonios}`;
    document.getElementById("geralResp2").textContent = `KM inicial: ${kmInicial}\nKM final: ${kmFinal}\nHorário de chegada: ${horaChegada}\nHorário de saída: ${horaSaida}\nEndereço de partida: ${enderecoPartida}\nEndereço de chegada: ${enderecoChegada}\nDescrição: ${informacoesAdicionais}`;
  }
  
  // Função para copiar o texto do modal e abrir o WhatsApp para envio
  function copResp2() {
    // Atualizar informações antes de copiar
    infoGeral();
  
    const geralResp0 = document.getElementById("geralResp0").textContent;
    const geralResp1 = document.getElementById("geralResp1").textContent;
    const geralResp2 = document.getElementById("geralResp2").textContent;
  
    const textoCompleto = `${geralResp0}\n${geralResp1}\n${geralResp2}`;
  
    // Copiar para a área de transferência
    navigator.clipboard.writeText(textoCompleto)
      .then(() => {
        // Abrir WhatsApp para envio
        const textoEncoded = encodeURIComponent(textoCompleto);
        const whatsappURL = `https://wa.me/?text=${textoEncoded}`;
        window.open(whatsappURL, "_blank");
      })
      .catch(err => console.error("Erro ao copiar o texto: ", err));
  }
  
  // Função para apagar todos os campos do formulário
  function deleteRespGeral() {
    const inputs = document.querySelectorAll("#scriptForm input, #scriptForm textarea");
    inputs.forEach(input => input.value = "");
  
    // Limpar os parágrafos do modal
    document.getElementById("geralResp0").textContent = "";
    document.getElementById("geralResp1").textContent = "";
    document.getElementById("geralResp2").textContent = "";
  
    // Remover os dados do localStorage
    localStorage.removeItem("formData");
  }
  
  // Função para enviar os dados do formulário para o webhook do Discord
  function enviarParaDiscord() {
    return new Promise((resolve, reject) => {
        console.log("Enviando dados para o Discord...");
        const formData = JSON.parse(localStorage.getItem("formData"));
        const nomeUsuario = sessionStorage.getItem("nomeUsuario") || "Usuário desconhecido";
        const arquivoInput = document.getElementById("arquivo");
        const arquivo = arquivoInput.files[0];

        if (formData) {
            const embed = {
                title: "Script de atendimento",
                fields: [
                    { name: "Nome do técnico", value: nomeUsuario, inline: false },
                    { name: "Data do chamado", value: formData.dataChamado || "N/A", inline: false },
                    { name: "Número do chamado", value: formData.numeroChamado || "N/A", inline: false },
                    { name: "Cliente", value: formData.cliente || "N/A", inline: false },
                    { name: "Nome do informante", value: formData.nomeInformante || "N/A", inline: false },
                    { name: "Quantidade de patrimônios", value: formData.quantidadePatrimonios || "N/A", inline: false },
                    { name: "KM inicial", value: formData.kmInicial || "N/A", inline: false },
                    { name: "KM final", value: formData.kmFinal || "N/A", inline: false },
                    { name: "Hora de chegada", value: formData.horaChegada || "N/A", inline: false },
                    { name: "Hora de saída", value: formData.horaSaida || "N/A", inline: true },
                    { name: "Endereço de partida", value: formData.enderecoPartida || "N/A", inline: false },
                    { name: "Endereço de chegada", value: formData.enderecoChegada || "N/A", inline: false },
                    { name: "Informações adicionais", value: formData.informacoesAdicionais || "N/A", inline: false },
                ],
                color: 3066993,
                footer: {
                    text: 'Atenciosamente Sou + Tecnologia',
                    icon_url: 'https://i.imgur.com/sOsHaID.png'
                },
                timestamp: new Date().toISOString()
            };

            const webhookURL = "https://discord.com/api/webhooks/1326597466392498237/MdUd68kvPG4eQhiy7KB4KY0WiyzQQBSmsUwu4vOy19OKci0W5CihB8YTBh3_MJYmGyN2";
            const formDataToSend = new FormData();

            if (arquivo) {
                formDataToSend.append("file", arquivo, arquivo.name);
            }

            formDataToSend.append("payload_json", JSON.stringify({
                embeds: [embed]
            }));

            fetch(webhookURL, {
                method: "POST",
                body: formDataToSend
            })
            .then(response => {
                if (response.ok) {
                    console.log("Dados enviados para o Discord com sucesso.");
                    resolve();
                } else {
                    reject(new Error("Erro ao enviar dados para o Discord"));
                }
            })
            .catch(error => {
                console.error("Erro ao enviar dados para o Discord:", error);
                reject(error);
            });
        } else {
            reject(new Error("Nenhum dado encontrado no localStorage"));
        }
    });
  }
  
  // Função que combina salvar no banco e enviar para o Discord
  async function salvarEEnviar() {
    try {
        // Primeiro salva no banco
        const formData = new FormData(document.getElementById('scriptForm'));
        const response = await fetch('salvar_dados.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.text();
        
        if (!data.includes("sucesso")) {
            throw new Error(data);
        }

        // Se salvou no banco com sucesso, envia para o Discord
        await enviarParaDiscord();

        // Se ambas as operações foram bem-sucedidas, mostra mensagem de sucesso
        mostrarSucesso("Dados salvos no banco e enviados para o Discord com sucesso!");

    } catch (error) {
        console.error('Erro:', error);
        mostrarErro(`Erro durante o processo: ${error.message}`);
    }
  }
  
  // Carregar os dados do formulário ao carregar a página
  window.onload = carregarDadosFormulario;
  
  // Remover os event listeners antigos e adicionar o novo
  document.addEventListener('DOMContentLoaded', function() {
    // Remove os botões antigos se ainda existirem
    const btnSalvarBanco = document.getElementById('salvarBanco');
    const btnEnviarDiscord = document.getElementById('enviarDiscord');
    
    if (btnSalvarBanco) {
        btnSalvarBanco.remove();
    }
    if (btnEnviarDiscord) {
        btnEnviarDiscord.remove();
    }

    // Adiciona o event listener para o novo botão
    document.getElementById('salvarTudo').addEventListener('click', function(e) {
        e.preventDefault();
        infoGeral(); // Atualiza as informações antes de salvar
        salvarEEnviar();
    });
  });