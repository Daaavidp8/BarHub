﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models.Enums
{
    public enum OrderState
    {
        SERVIDO = 1,
        CANCELADO = 2,
        PREPARACION = 5
    }

    public enum Roles
    {
        ADMIN = 1,
        PROPIETARIO = 2,
        CAMARERO = 3,
        ENCARGADOBARRA = 4,
        COCINERO = 5,
    }
}
