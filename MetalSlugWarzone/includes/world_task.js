// Local installer adapter for Windows Script Host (JScript 5.8).
// Invoked only by loopback setup through windowless wscript.exe.
// No passwords, elevation, security-policy edits or shell commands.
(function () {
    if (typeof WScript === 'undefined') return;
    var args = WScript.Arguments;
    if (args.length !== 6) { WScript.Quit(2); return; }
    var action = args.Item(0), taskName = args.Item(1), xmlPath = args.Item(2);
    var outputPath = args.Item(3), sid = args.Item(4), mode = args.Item(5);
    var result = 0, message = '', fs = new ActiveXObject('Scripting.FileSystemObject');
    function quote(value) {
        return '"' + String(value).replace(/[\\"\x00-\x1f\u007f-\uffff]/g, function (c) {
            return '\\u' + ('0000' + c.charCodeAt(0).toString(16)).slice(-4);
        }) + '"';
    }
    try {
        if (!/^MetalSlugWarzone-[0-9a-f]+$/.test(taskName) || !/^S-1-\d+(?:-\d+)+$/.test(sid)) throw new Error('Invalid task identity.');
        var types = { InteractiveToken: 3, S4U: 2, ServiceAccount: 5 };
        if (!types[mode]) throw new Error('Invalid task logon type.');
        var scheduler = new ActiveXObject('Schedule.Service');
        scheduler.Connect();
        var folder = scheduler.GetFolder('\\');
        if (action === 'register') {
            var input = fs.OpenTextFile(xmlPath, 1, false, -1);
            var definition = input.ReadAll(); input.Close();
            folder.RegisterTask(taskName, definition, 6, sid, null, types[mode], null);
            message = 'Automatic startup registered.';
        } else if (action === 'run') {
            folder.GetTask(taskName).Run(null);
            message = 'Automatic startup requested.';
        } else {
            throw new Error('Invalid task action.');
        }
    } catch (error) {
        result = 1;
        var code = typeof error.number === 'number' ? ' (0x' + ('00000000' + (error.number >>> 0).toString(16)).slice(-8) + ')' : '';
        message = (error.description || error.message || String(error)) + code;
    }
    var output = fs.CreateTextFile(outputPath, true, true);
    output.Write('{"exit":' + result + ',"output":' + quote(message) + '}');
    output.Close();
    WScript.Quit(result);
}());
