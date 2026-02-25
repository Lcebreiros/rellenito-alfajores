const express = require('express');
const cors = require('cors');
const escpos = require('escpos');

// Adapters
escpos.USB = require('escpos-usb');

const app = express();
const PORT = process.env.PORT || 9876;

// Middleware
app.use(cors());
app.use(express.json({ limit: '10mb' }));

// Configuración de la impresora (se puede cambiar según tu impresora)
const PRINTER_CONFIG = {
  encoding: 'UTF-8',
  width: 48, // Ancho en caracteres (ajustar según tu impresora)
};

/**
 * Encuentra la primera impresora USB disponible
 */
function findPrinter() {
  try {
    const devices = escpos.USB.findPrinter();

    if (!devices || devices.length === 0) {
      console.error('No se encontraron impresoras USB');
      return null;
    }

    console.log(`Impresoras encontradas: ${devices.length}`);
    devices.forEach((device, index) => {
      console.log(`  [${index}] VID: ${device.deviceDescriptor.idVendor}, PID: ${device.deviceDescriptor.idProduct}`);
    });

    // Usar la primera impresora encontrada
    return new escpos.USB(devices[0].deviceDescriptor.idVendor, devices[0].deviceDescriptor.idProduct);
  } catch (error) {
    console.error('Error al buscar impresoras:', error.message);
    return null;
  }
}

/**
 * Imprime texto plano en la impresora térmica
 */
function printPlainText(text) {
  return new Promise((resolve, reject) => {
    const device = findPrinter();

    if (!device) {
      return reject(new Error('No se encontró ninguna impresora USB conectada'));
    }

    try {
      const printer = new escpos.Printer(device, PRINTER_CONFIG);

      device.open((error) => {
        if (error) {
          console.error('Error al abrir dispositivo:', error);
          return reject(error);
        }

        try {
          printer
            .font('a')
            .align('ct')
            .style('normal')
            .size(1, 1)
            .text(text)
            .cut()
            .close(() => {
              console.log('Impresión completada');
              resolve({ success: true });
            });
        } catch (printError) {
          console.error('Error durante la impresión:', printError);
          reject(printError);
        }
      });
    } catch (error) {
      console.error('Error al crear impresora:', error);
      reject(error);
    }
  });
}

/**
 * Imprime un ticket con formato
 */
function printTicket(ticketData) {
  return new Promise((resolve, reject) => {
    const device = findPrinter();

    if (!device) {
      return reject(new Error('No se encontró ninguna impresora USB conectada'));
    }

    try {
      const printer = new escpos.Printer(device, PRINTER_CONFIG);

      device.open((error) => {
        if (error) {
          console.error('Error al abrir dispositivo:', error);
          return reject(error);
        }

        try {
          // Encabezado con nombre del negocio
          const businessName = ticketData.business_name || ticketData.app_name || 'Estacionamiento Moreno S.R.L.';

          printer
            .font('a')
            .align('ct')
            .style('b')
            .size(1, 1)
            .text('')
            .text(businessName)
            .style('normal')
            .drawLine()
            .text('');

          // Datos del vehículo (alineado a la izquierda)
          printer
            .align('lt')
            .text(`Patente: ${ticketData.license_plate}`)
            .text(`Vehículo: ${ticketData.vehicle_type}`)
            .text(`Cochera: ${ticketData.space_name}`)
            .text('')
            .text(`Fecha: ${ticketData.entry_at}`)
            .text('');

          // Código de barras (si está disponible)
          if (ticketData.barcode) {
            printer
              .align('ct')
              .barcode(ticketData.barcode, 'CODE39', {
                width: 2,
                height: 50,
                position: 'below',
                font: 'a',
                includeParity: false,
              })
              .text('');
          }

          printer.drawLine();

          // Pie
          printer
            .align('ct')
            .text('Conserve este ticket')
            .text('Gracias por su visita')
            .text('')
            .text('')
            .text('')
            .cut()
            .close(() => {
              console.log('Ticket impreso correctamente');
              resolve({ success: true });
            });
        } catch (printError) {
          console.error('Error durante la impresión:', printError);
          reject(printError);
        }
      });
    } catch (error) {
      console.error('Error al crear impresora:', error);
      reject(error);
    }
  });
}

// Rutas API

/**
 * GET /
 * Estado del servidor
 */
app.get('/', (req, res) => {
  const device = findPrinter();
  res.json({
    status: 'running',
    printer_connected: device !== null,
    port: PORT,
  });
});

/**
 * GET /printers
 * Lista impresoras disponibles
 */
app.get('/printers', (req, res) => {
  try {
    const devices = escpos.USB.findPrinter();

    if (!devices || devices.length === 0) {
      return res.json({ printers: [], count: 0 });
    }

    const printerList = devices.map((device, index) => ({
      index,
      vendorId: device.deviceDescriptor.idVendor,
      productId: device.deviceDescriptor.idProduct,
    }));

    res.json({ printers: printerList, count: printerList.length });
  } catch (error) {
    console.error('Error al listar impresoras:', error);
    res.status(500).json({ error: error.message });
  }
});

/**
 * POST /print/text
 * Imprime texto plano
 * Body: { text: "texto a imprimir" }
 */
app.post('/print/text', async (req, res) => {
  const { text } = req.body;

  if (!text) {
    return res.status(400).json({ error: 'El campo "text" es requerido' });
  }

  try {
    await printPlainText(text);
    res.json({ success: true, message: 'Texto impreso correctamente' });
  } catch (error) {
    console.error('Error al imprimir:', error);
    res.status(500).json({ error: error.message });
  }
});

/**
 * POST /print/ticket
 * Imprime un ticket de parking
 * Body: { ticket_data: {...} }
 */
app.post('/print/ticket', async (req, res) => {
  const { ticket_data } = req.body;

  if (!ticket_data) {
    return res.status(400).json({ error: 'El campo "ticket_data" es requerido' });
  }

  try {
    await printTicket(ticket_data);
    res.json({ success: true, message: 'Ticket impreso correctamente' });
  } catch (error) {
    console.error('Error al imprimir ticket:', error);
    res.status(500).json({ error: error.message });
  }
});

/**
 * POST /test
 * Imprime un ticket de prueba
 */
app.post('/test', async (req, res) => {
  const testTicket = {
    stay_id: 999,
    license_plate: 'ABC123',
    vehicle_type: 'Auto',
    space_name: 'A1',
    entry_at: new Date().toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' }),
    barcode: '0000000999',
    business_name: 'Estacionamiento Moreno S.R.L.',
    app_name: 'Gestior',
  };

  try {
    await printTicket(testTicket);
    res.json({ success: true, message: 'Ticket de prueba impreso' });
  } catch (error) {
    console.error('Error al imprimir ticket de prueba:', error);
    res.status(500).json({ error: error.message });
  }
});

// Iniciar servidor
app.listen(PORT, () => {
  console.log(`========================================`);
  console.log(`Servidor de impresión térmica iniciado`);
  console.log(`Puerto: ${PORT}`);
  console.log(`URL: http://localhost:${PORT}`);
  console.log(`========================================`);
  console.log('');

  // Verificar impresoras al inicio
  const device = findPrinter();
  if (device) {
    console.log('✓ Impresora USB detectada y lista');
  } else {
    console.log('⚠ No se detectaron impresoras USB');
    console.log('  Conecta una impresora y reinicia el servidor');
  }
  console.log('');
});

// Manejo de errores
process.on('uncaughtException', (error) => {
  console.error('Error no capturado:', error);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('Promesa rechazada no manejada:', reason);
});
