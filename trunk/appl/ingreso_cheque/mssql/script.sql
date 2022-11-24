create table CHEQUE
(
    COD_CHEQUE                 numeric(10)           identity,
    COD_INGRESO_CHEQUE         numeric(10)           null    ,
    COD_BANCO                  numeric(10)           not null,
    COD_PLAZA                  numeric(10)           not null,
    COD_TIPO_DOC_PAGO          numeric(10)           null    ,
    NRO_DOC                    numeric(10)           not null,
    FECHA_DOC                  datetime              not null,
    MONTO_DOC                  numeric(10)           not null,
    constraint PK_CHEQUE primary key (COD_CHEQUE)
)
go

create index IND_CHEQUE1 on CHEQUE (COD_INGRESO_CHEQUE)
go

create table INGRESO_CHEQUE
(
    COD_INGRESO_CHEQUE         numeric(10)           identity,
    FECHA_INGRESO_CHEQUE       datetime              not null,
    COD_USUARIO                numeric(3)            not null,
    COD_EMPRESA                numeric(10)           not null,
    COD_ESTADO_INGRESO_CHEQUE  numeric(10)           null    ,
    REFERENCIA                 varchar(100)          null    ,
    constraint PK_INGRESO_CHEQUE primary key (COD_INGRESO_CHEQUE)
)
go

create index IND_INGCHEQUE1 on INGRESO_CHEQUE (COD_EMPRESA)
go

create table ESTADO_INGRESO_CHEQUE
(
    COD_ESTADO_INGRESO_CHEQUE  numeric(10)           not null,
    NOM_ESTADO_INGRESO_CHEQUE  varchar(100)          not null,
    constraint PK_ESTADO_INGRESO_CHEQUE primary key (COD_ESTADO_INGRESO_CHEQUE)
)
go

alter table CHEQUE
    add constraint FK_BANCO_CHEQUE foreign key  (COD_BANCO)
       references BANCO (COD_BANCO)
go

alter table CHEQUE
    add constraint FK_PLAZA_CHEQUE foreign key  (COD_PLAZA)
       references PLAZA (COD_PLAZA)
go

alter table CHEQUE
    add constraint FK_INGCHEQUE_CHEQUE foreign key  (COD_INGRESO_CHEQUE)
       references INGRESO_CHEQUE (COD_INGRESO_CHEQUE)
go

alter table CHEQUE
    add constraint FK_TDOC_CHEQUE foreign key  (COD_TIPO_DOC_PAGO)
       references TIPO_DOC_PAGO (COD_TIPO_DOC_PAGO)
go

alter table INGRESO_CHEQUE
    add constraint FK_USU_INGCHEQUE foreign key  (COD_USUARIO)
       references USUARIO (COD_USUARIO)
go

alter table INGRESO_CHEQUE
    add constraint FK_EMP_INGCHEQUE foreign key  (COD_EMPRESA)
       references EMPRESA (COD_EMPRESA)
go

alter table INGRESO_CHEQUE
    add constraint FK_ESTINGCHEQUE_INGCHEQUE foreign key  (COD_ESTADO_INGRESO_CHEQUE)
       references ESTADO_INGRESO_CHEQUE (COD_ESTADO_INGRESO_CHEQUE)
go



 
 --/////////////////////////////////////
 insert into item_menu values('2575'		,'Registro Cheque Clientes'	,'S', 'M', 'S');
go
insert AUTORIZA_MENU select cod_perfil, '2575', 'E', 'S','S' from PERFIL where COD_PERFIL = 1
go
insert AUTORIZA_MENU select cod_perfil, '2575', 'N', 'N','N' from PERFIL where COD_PERFIL <> 1
go

--////////////// PERMISOS ESPECIALES /////////////

insert into ITEM_MENU values(9970,'Registro Cheque Clientes','N','M','S')
insert into ITEM_MENU values(997005,'Modificar Estado','N','M','S')

insert into AUTORIZA_MENU(COD_PERFIL,COD_ITEM_MENU,AUTORIZA_MENU,IMPRESION,EXPORTAR)
select COD_PERFIL,9970,'E','S','S' from PERFIL where COD_PERFIL = 1

insert into AUTORIZA_MENU(COD_PERFIL,COD_ITEM_MENU,AUTORIZA_MENU,IMPRESION,EXPORTAR)
select COD_PERFIL,997005,'E','S','S' from PERFIL where COD_PERFIL = 1


insert into AUTORIZA_MENU(COD_PERFIL,COD_ITEM_MENU,AUTORIZA_MENU,IMPRESION,EXPORTAR)
select COD_PERFIL,9970,'N','N','N' from PERFIL where COD_PERFIL <> 1

insert into AUTORIZA_MENU(COD_PERFIL,COD_ITEM_MENU,AUTORIZA_MENU,IMPRESION,EXPORTAR)
select COD_PERFIL,997005,'N','N','N' from PERFIL where COD_PERFIL <> 1
--////////////// FIN PERMISOS ESPECIALES /////////////