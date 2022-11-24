CREATE TABLE ARR_X_FACTURAR(
                COD_ARRIENDO NUMERIC(10),
                NOM_ARRIENDO varchar(100),
                REFERENCIA varchar(100),
                TOTAL T_PRECIO,
                NOM_EMPRESA varchar(100),
                RUT  NUMERIC(10),
                DIG_VERIF varchar(1),
                COD_USUARIO numeric (10),
                CONSTRAINT PK_ARR_X_FACTURAR PRIMARY KEY (COD_ARRIENDO)
                )