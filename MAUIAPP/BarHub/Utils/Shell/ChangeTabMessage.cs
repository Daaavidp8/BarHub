﻿using CommunityToolkit.Mvvm.Messaging.Messages;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Utils.Shell
{
    public class ChangeTabMessage : ValueChangedMessage<string>
    {
        public ChangeTabMessage(string value) : base(value) { }
    }
}
