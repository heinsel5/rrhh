# Sistema de Gestión de RRHH - README
Descripción
Sistema backend para gestión de recursos humanos desarrollado con Laravel 11 usando TDD (Desarrollo guiado por pruebas).

## Funcionalidades
Registrar colaboradores

## Crear contratos

- **Registrar prórrogas (tiempo y valor)**

- **Terminar contratos anticipadamente**

- **Roles y permisos**

## Tecnologías
- **Tecnología	Versión**
- **PHP	8.4+**
- **Laravel	11**
- **MySQL/MariaDB	8.0+**
- **PHPUnit	10+**
- 
## Casos de Prueba
### CP	Módulo	Pruebas
- **CP-001	Colaboradores	Crear, listar, actualizar, eliminar**
- **CP-002	Contratos	Crear, validar fechas/salario, actualizar**
- **CP-003	Prórrogas	Tiempo, valor, rechazar si está terminado**
- **CP-004	Terminaciones	Cambiar estado, registrar motivo, evitar duplicados**

## Instalación Rápida
bash
## 1. Clonar
git clone https://github.com/heinssel5/rrhh.git
cd rrhh-sistema

## 2. Instalar dependencias
composer install

## 3. Configurar .env
cp .env.example .env
php artisan key:generate

## 4. Configurar base de datos en .env
DB_DATABASE=rrhh_sistema
DB_USERNAME=root
DB_PASSWORD=

## 5. Ejecutar pruebas
php artisan test

## Todas las pruebas
php artisan test

## Pruebas específicas
- **php artisan test --filter CollaboratorTest**

- **php artisan test --filter ManageContractsTest**

- **php artisan test --filter RegisterContractExtensionTest**

- **php artisan test --filter TerminateContractTest**

## Estructura Principal
text
app/Models/
├── Collaborator.php
├── Contract.php
├── ContractExtension.php
└── ContractTermination.php

tests/Feature/
├── CollaboratorTest.php
├── ManageContractsTest.php
├── RegisterContractExtensionTest.php
└── TerminateContractTest.php

database/migrations/
├── create_collaborators_table.php
├── create_contracts_table.php
├── create_contract_extensions_table.php
└── create_contract_terminations_table.php
## Roles y Permisos
### Roles
Gestor RRHH - Acceso completo

Consultor - Solo lectura

Administrador - Acceso total

## Modelos y Relaciones

text
Collaborator (1) ----< (N) Contract             
Contract (1) ----< (N) ContractExtension
Contract (1) ---- (1) ContractTermination

API Endpoints
Método	Endpoint	Descripción
GET	/api/collaborators	Listar colaboradores
POST	/api/collaborators	Crear colaborador
PUT	/api/collaborators/{id}	Actualizar colaborador
DELETE	/api/collaborators/{id}	Eliminar colaborador
POST	/api/contracts	Crear contrato
PUT	/api/contracts/{id}	Actualizar contrato
POST	/api/extensions	Crear prórroga
POST	/api/terminations	Terminar contrato
Comandos Útiles
bash

# Autor
- **Heinsel Molina - Desarrollo backend con Laravel y TDD**
