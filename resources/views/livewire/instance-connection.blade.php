<div style="display:inline">

    <!-- Button -->
    <button class="btn btn-warning" id="submit-button">
        Probar conexión
    </button>


    <script type="module">

        document.querySelector('#submit-button').addEventListener('click', function(event) {
            event.preventDefault();

            const name = document.querySelector('#name').value;
            const route = document.querySelector('#route').value;
            const host = document.querySelector('#host').value;
            const port = document.querySelector('#port').value;
            const username = document.querySelector('#username').value;
            const password = document.querySelector('#password').value;

            const data = {
                name: name,
                route: route,
                host: host,
                port: port,
                username: username,
                password: password
            };

            const jsonString = JSON.stringify(data);

            Livewire.emit('testConnection', jsonString)

        });

        Livewire.on('testConnectionResult', result => {

            if(result){
                $('#save_instance').attr('disabled', false)
                $('#submit-button')
                    .removeAttr('class')
                    .addClass('btn btn-success')
                    .text('Conexión realizada con éxito');
            } else {
                $('#save_instance').attr('disabled', true)
                $('#submit-button')
                    .removeAttr('class')
                    .addClass('btn btn-danger')
                    .text('Error en la conexión con la base, prueba de nuevo');
            }

        })


    </script>

</div>
