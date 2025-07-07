                </div>
            </div>
        </div>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AdminLTE 3 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.0/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json'
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
            });
            
            // Controle responsivo do menu
            function initResponsiveMenu() {
                const $body = $('body');
                const $sidebar = $('.main-sidebar');
                const $overlay = $('.sidebar-overlay');
                const $pushMenuBtn = $('[data-widget="pushmenu"]');
                
                // Função para abrir menu
                function openSidebar() {
                    $body.addClass('sidebar-open');
                }
                
                // Função para fechar menu
                function closeSidebar() {
                    $body.removeClass('sidebar-open');
                }
                
                // Event listeners
                $pushMenuBtn.on('click', function(e) {
                    e.preventDefault();
                    if ($body.hasClass('sidebar-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
                
                // Fechar menu ao clicar no overlay
                $overlay.on('click', function() {
                    closeSidebar();
                });
                
                // Fechar menu ao redimensionar para desktop
                $(window).on('resize', function() {
                    if ($(window).width() > 768) {
                        closeSidebar();
                    }
                });
                
                // Fechar menu ao clicar em links do menu (mobile)
                $('.nav-sidebar .nav-link').on('click', function() {
                    if ($(window).width() <= 768) {
                        closeSidebar();
                    }
                });
            }
            
            // Inicializar menu responsivo
            initResponsiveMenu();
        });

        // Função para confirmar exclusão
        function confirmarExclusao(id, tipo) {
            Swal.fire({
                title: 'Confirmar exclusão?',
                text: `Deseja realmente excluir este ${tipo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `action${tipo}.php?acao=excluir&id=${id}`;
                }
            });
        }

        // Função para mostrar mensagens
        function mostrarMensagem(tipo, mensagem) {
            Swal.fire({
                icon: tipo,
                title: mensagem,
                timer: 3000,
                showConfirmButton: false
            });
        }
    </script>
</body>
</html> 