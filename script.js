// Função para enviar para o webhook do Discord
async function sendToDiscord(content) {
  const webhookUrl = 'https://discord.com/api/webhooks/1338854980920279071/rXnr0N7Byj1pY_7_T7UcKyM6Oc7QeAPiDDZVkBtGHt3zooXaX0b2SyROlpExvaJFoJSi';

  // Extrair os dados diretamente dos campos do formulário
  const dados = {
    dataChamado: document.getElementById("dataChamado").value || 'Não informado',
    numeroChamado: document.getElementById("numeroChamado").value || 'Não informado',
    tipoChamado: document.getElementById("tipoChamado").value || 'Não informado',
    cliente: document.getElementById("cliente").value || 'Não informado',
    quantidadePatrimonios: document.getElementById("quantidadePatrimonios").value || 'Não informado',
    kmInicial: document.getElementById("kmInicial").value || 'Não informado',
    kmFinal: document.getElementById("kmFinal").value || 'Não informado',
    horaChegada: document.getElementById("horaChegada").value || 'Não informado',
    horaSaida: document.getElementById("horaSaida").value || 'Não informado',
    enderecoPartida: document.getElementById("enderecoPartida").value || 'Não informado',
    enderecoChegada: document.getElementById("enderecoChegada").value || 'Não informado',
    descricaoChamado: document.getElementById("descricaoChamado").value || 'Sem descrição',
    nomeInformante: document.getElementById("nomeInformante").value || 'Não informado',
    statusChamado: document.getElementById("statusChamado").value || 'Não informado'
  };

  // Criar o embed com os dados formatados
  const embed = {
    title: "📋 Novo Chamado Registrado",
    color: 0x00ff00, // Cor verde
    fields: [
      {
        name: "📅 Data e Número",
        value: `Data: ${dados.dataChamado}\nNº: ${dados.numeroChamado}`,
        inline: true
      },
      {
        name: "📋 Tipo e Status",
        value: `Tipo: ${dados.tipoChamado}\nStatus: ${dados.statusChamado}`,
        inline: true
      },
      {
        name: "👥 Cliente",
        value: dados.cliente,
        inline: true
      },
      {
        name: "🔧 Patrimônios",
        value: dados.quantidadePatrimonios,
        inline: true
      },
      {
        name: "🚗 Quilometragem",
        value: `Inicial: ${dados.kmInicial}\nFinal: ${dados.kmFinal}`,
        inline: true
      },
      {
        name: "⏰ Horários",
        value: `Chegada: ${dados.horaChegada}\nSaída: ${dados.horaSaida}`,
        inline: true
      },
      {
        name: "📍 Endereços",
        value: `De: ${dados.enderecoPartida}\nPara: ${dados.enderecoChegada}`,
        inline: false
      },
      {
        name: "📝 Descrição do Chamado",
        value: dados.descricaoChamado,
        inline: false
      },
      {
        name: "👤 Informante",
        value: dados.nomeInformante,
        inline: true
      }
    ],
    timestamp: new Date().toISOString(),
    footer: {
      text: "Sistema de Registro de Chamados"
    }
  };

  // Criar o texto formatado para cópia fácil
  const textoFormatado = "\`\`\`" + 
    `Data do chamado: ${dados.dataChamado}\n` +
    `Nº do chamado: ${dados.numeroChamado}\n` +
    `Tipo de chamado: ${dados.tipoChamado}\n` +
    `Cliente: ${dados.cliente}\n` +
    `Quantidade de patrimônios: ${dados.quantidadePatrimonios}\n` +
    `KM inicial: ${dados.kmInicial}\n` +
    `KM final: ${dados.kmFinal}\n` +
    `Horário de chegada: ${dados.horaChegada}\n` +
    `Horário de saída: ${dados.horaSaida}\n` +
    `Endereço de partida: ${dados.enderecoPartida}\n` +
    `Endereço de chegada: ${dados.enderecoChegada}\n` +
    `Descrição: ${dados.descricaoChamado}\n` +
    `Informante: ${dados.nomeInformante}\n` +
    `Status: ${dados.statusChamado}\n` +
    "\`\`\`";

  try {
    console.log('Enviando mensagem para o Discord...');
    console.log('Dados:', dados);

    const response = await fetch(webhookUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        content: "**Copie os dados abaixo:**\n" + textoFormatado,
        embeds: [embed],
        username: 'Relatório de Chamado',
        avatar_url: 'https://example.com/avatar.png',
      }),
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Erro ao enviar para o Discord: ${response.status} - ${errorText}`);
    }

    // Se a resposta for 204 (No Content) ou estiver vazia, consideramos sucesso
    if (response.status === 204 || response.headers.get("content-length") === "0") {
      console.log('Mensagem enviada com sucesso (resposta vazia)');
      return true;
    }

    // Tenta fazer o parse do JSON apenas se houver conteúdo
    const responseText = await response.text();
    if (responseText) {
      try {
        const responseData = JSON.parse(responseText);
        console.log('Mensagem enviada com sucesso:', responseData);
        return responseData;
      } catch (jsonError) {
        console.warn('Resposta não é um JSON válido, mas o envio foi bem-sucedido');
        return true;
      }
    }

    return true;
  } catch (error) {
    console.error('Erro ao enviar para o Discord:', error);
    throw new Error(`Falha no envio: ${error.message}`);
  }
}

// Função para salvar dados do formulário
function salvarDadosFormulario() {
  const formData = {
    dataChamado: document.getElementById("dataChamado").value,
    numeroChamado: document.getElementById("numeroChamado").value,
    tipoChamado: document.getElementById("tipoChamado").value,
    cliente: document.getElementById("cliente").value,
    quantidadePatrimonios: document.getElementById("quantidadePatrimonios").value,
    kmInicial: document.getElementById("kmInicial").value,
    kmFinal: document.getElementById("kmFinal").value,
    horaChegada: document.getElementById("horaChegada").value,
    horaSaida: document.getElementById("horaSaida").value,
    enderecoPartida: document.getElementById("enderecoPartida").value,
    enderecoChegada: document.getElementById("enderecoChegada").value,
    descricaoChamado: document.getElementById("descricaoChamado").value,
    nomeInformante: document.getElementById("nomeInformante").value,
    statusChamado: document.getElementById("statusChamado").value
  };
  localStorage.setItem("formData", JSON.stringify(formData));
}

// Função para carregar dados do formulário
function carregarDadosFormulario() {
  const formData = JSON.parse(localStorage.getItem("formData"));
  if (formData) {
    document.getElementById("dataChamado").value = formData.dataChamado || "";
    document.getElementById("numeroChamado").value = formData.numeroChamado || "";
    document.getElementById("tipoChamado").value = formData.tipoChamado || "";
    document.getElementById("cliente").value = formData.cliente || "";
    document.getElementById("quantidadePatrimonios").value = formData.quantidadePatrimonios || "";
    document.getElementById("kmInicial").value = formData.kmInicial || "";
    document.getElementById("kmFinal").value = formData.kmFinal || "";
    document.getElementById("horaChegada").value = formData.horaChegada || "";
    document.getElementById("horaSaida").value = formData.horaSaida || "";
    document.getElementById("enderecoPartida").value = formData.enderecoPartida || "";
    document.getElementById("enderecoChegada").value = formData.enderecoChegada || "";
    document.getElementById("descricaoChamado").value = formData.descricaoChamado || "";
    document.getElementById("nomeInformante").value = formData.nomeInformante || "";
    document.getElementById("statusChamado").value = formData.statusChamado || "";
  }
}

// Função para coletar informações gerais
function infoGeral() {
  salvarDadosFormulario();

  const dataChamado = document.getElementById("dataChamado").value;
  const numeroChamado = document.getElementById("numeroChamado").value;
  const tipoChamado = document.getElementById("tipoChamado").value;
  const cliente = document.getElementById("cliente").value;
  const quantidadePatrimonios = document.getElementById("quantidadePatrimonios").value;
  const kmInicial = document.getElementById("kmInicial").value;
  const kmFinal = document.getElementById("kmFinal").value;
  const horaChegada = document.getElementById("horaChegada").value;
  const horaSaida = document.getElementById("horaSaida").value;
  const enderecoPartida = document.getElementById("enderecoPartida").value;
  const enderecoChegada = document.getElementById("enderecoChegada").value;
  const descricaoChamado = document.getElementById("descricaoChamado").value;
  const nomeInformante = document.getElementById("nomeInformante").value;
  const statusChamado = document.getElementById("statusChamado").value;

  // Gerar texto formatado
  const textoCompleto = 
    `📅 *Data do chamado:* ${dataChamado}\n` +
    `🔢 *Nº do chamado:* ${numeroChamado}\n` +
    `📋 *Tipo de chamado:* ${tipoChamado}\n` +
    `👥 *Cliente:* ${cliente}\n` +
    `🔧 *Quantidade de patrimônios tratados:* ${quantidadePatrimonios}\n` +
    `🚗 *KM inicial:* ${kmInicial}\n` +
    `🚗 *KM final:* ${kmFinal}\n` +
    `⏰ *Horário de chegada:* ${horaChegada}\n` +
    `⏰ *Horário de saída:* ${horaSaida}\n` +
    `📍 *Endereço de partida:* ${enderecoPartida}\n` +
    `📍 *Endereço de chegada:* ${enderecoChegada}\n` +
    `📝 *Descrição do chamado:*\n${descricaoChamado}\n` +
    `👤 *Nome de quem informou:* ${nomeInformante}\n` +
    `📊 *Status do chamado:* ${statusChamado}`;

  return textoCompleto;
}

// Função para mostrar modal de sucesso
function showSuccessModal(message) {
  const modalBody = document.getElementById('successModalBody');
  modalBody.textContent = message;
  const modal = new bootstrap.Modal(document.getElementById('successModal'));
  modal.show();
}

// Função para mostrar modal de erro
function showErrorModal(message) {
  const modalBody = document.getElementById('errorModalBody');
  modalBody.textContent = message;
  const modal = new bootstrap.Modal(document.getElementById('errorModal'));
  modal.show();
}

// Função para apagar todos os campos
function deleteRespGeral() {
  try {
    const btn = document.querySelector('button[onclick="deleteRespGeral()"]');
    const btnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Limpando...';

    const inputs = document.querySelectorAll("#scriptForm input, #scriptForm textarea, #scriptForm select");
    inputs.forEach(input => input.value = "");
    localStorage.removeItem("formData");

    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = btnText;
      showSuccessModal("Todos os campos foram limpos!");
    }, 500);
  } catch (err) {
    console.error("Erro ao limpar campos:", err);
    showErrorModal("Erro ao limpar os campos");
  }
}

// Função unificada para enviar relatório
async function enviarRelatorio() {
  try {
    // Mostrar indicador de carregamento
    const btn = document.querySelector('button[onclick="enviarRelatorio()"]');
    const btnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';

    // Enviar para o Discord
    console.log("Enviando para o Discord...");
    await sendToDiscord(infoGeral());
    console.log("Mensagem enviada para o Discord com sucesso!");

    // Preparar texto para o WhatsApp
    console.log("Preparando envio para WhatsApp...");
    const textoWhatsApp = 
      `*Relatório de Chamado*\n\n` +
      `*Data do chamado:* ${document.getElementById("dataChamado").value || 'Não informado'}\n` +
      `*Nº do chamado:* ${document.getElementById("numeroChamado").value || 'Não informado'}\n` +
      `*Tipo de chamado:* ${document.getElementById("tipoChamado").value || 'Não informado'}\n` +
      `*Cliente:* ${document.getElementById("cliente").value || 'Não informado'}\n` +
      `*Quantidade de patrimônios:* ${document.getElementById("quantidadePatrimonios").value || 'Não informado'}\n` +
      `*KM inicial:* ${document.getElementById("kmInicial").value || 'Não informado'}\n` +
      `*KM final:* ${document.getElementById("kmFinal").value || 'Não informado'}\n` +
      `*Horário de chegada:* ${document.getElementById("horaChegada").value || 'Não informado'}\n` +
      `*Horário de saída:* ${document.getElementById("horaSaida").value || 'Não informado'}\n` +
      `*Endereço de partida:* ${document.getElementById("enderecoPartida").value || 'Não informado'}\n` +
      `*Endereço de chegada:* ${document.getElementById("enderecoChegada").value || 'Não informado'}\n` +
      `*Descrição:* ${document.getElementById("descricaoChamado").value || 'Sem descrição'}\n` +
      `*Informante:* ${document.getElementById("nomeInformante").value || 'Não informado'}\n` +
      `*Status:* ${document.getElementById("statusChamado").value || 'Não informado'}`;

    // Abrir WhatsApp em uma nova aba
    console.log("Abrindo WhatsApp...");
    const textoEncoded = encodeURIComponent(textoWhatsApp);
    const whatsappUrl = `https://api.whatsapp.com/send?text=${textoEncoded}`;
    window.open(whatsappUrl, "_blank");

    // Restaurar botão e mostrar mensagem de sucesso
    btn.disabled = false;
    btn.innerHTML = btnText;
    showSuccessModal("Relatório enviado com sucesso para Discord e WhatsApp!");

  } catch (err) {
    console.error("Erro ao enviar relatório:", err);
    showErrorModal(`Falha ao enviar relatório: ${err.message}`);
    
    // Restaurar botão em caso de erro
    const btn = document.querySelector('button[onclick="enviarRelatorio()"]');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send"></i> Enviar Relatório';
  }
}

// Carregar dados ao iniciar
window.onload = carregarDadosFormulario;
