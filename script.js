// Função para coletar as informações gerais e atualizar os elementos no modal
function infoGeral() {
  const dataChamado = document.getElementById("dataChamado").value;
  const numeroChamado = document.getElementById("numeroChamado").value;
  const cliente = document.getElementById("cliente").value;
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
  document.getElementById("geralResp1").textContent = `Cliente: ${cliente}\nQuantidade de patrimônios tratados: ${quantidadePatrimonios}`;
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
}
