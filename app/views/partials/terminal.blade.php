<div class="god">
    <div class="terminal-chat" x-data="terminalChat" x-init="showWelcome();">
        <div class="terminal-chat__header">
            <span class="terminal-chat__header-label">[ LOGOS TERMINAL v0.1 ]</span>
        </div>

        <div class="terminal-chat__log" x-ref="log">
            <template x-for="entry in history" :key="entry.id">
                <div class="terminal-chat__entry">
                    <div class="terminal-chat__entry-row">
                        <div class="terminal-chat__command-line">
                            <span class="terminal-chat__prompt">$</span>
                            <span class="terminal-chat__command" x-text="entry.command"></span>
                        </div>
                        <div class="terminal-chat__timestamp" x-text="entry.timestamp"></div>
                    </div>
                    <div class="terminal-chat__entry-row">
                        <div class="terminal-chat__response" x-html="entry.response"
                            style="white-space: pre-wrap; font-family: monospace">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="terminal-chat__input-line">
            <span class="terminal-chat__prompt">$</span>
            <input type="text" class="terminal-chat__input" placeholder="Enter command..." x-model="input"
                @keydown.enter="handleCommand" />
        </div>
    </div>

    <script>
        const welcomeMessage = `Welcome to Logos CLI\nType /help for available commands.`;

        document.addEventListener('alpine:init', () => {
            Alpine.data('terminalChat', () => ({
                input: '',
                history: [],
                idCounter: 0,

                showWelcome() {
                    this.addEntry(welcomeMessage, '', 10);
                },

                pushEntry(command = '') {
                    const id = this.idCounter++;
                    const timestamp = new Date().toLocaleString();
                    this.history.push({
                        id,
                        command,
                        response: '',
                        timestamp
                    });
                    return id;
                },

                async addEntry(text, command = '', delay = 10) {
                    const id = this.pushEntry(command);
                    await this.typewriter(id, text, delay);
                    this.scrollToBottom();
                },

                async typewriter(id, text, delay = 10) {
                    this.history = this.history.map(entry => {
                        if (entry.id === id) entry.response = '';
                        return entry;
                    });

                    let i = 0;
                    while (i < text.length) {
                        this.history = this.history.map(entry => {
                            if (entry.id === id) {
                                entry.response += text[i] === '\n' ? '<br>' : text[i];
                            }
                            return entry;
                        });
                        i++;
                        await this.sleep(delay);
                    }
                },

                sleep(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        this.$refs.log.scrollTop = this.$refs.log.scrollHeight;
                    });
                },

                async handleCommand() {
                    const cmd = this.input.trim();
                    if (!cmd) return;

                    const id = this.pushEntry(cmd);
                    this.input = '';
                    this.scrollToBottom();

                    if (cmd.startsWith('/')) {
                        if (cmd === '/help') {
                            await this.typewriter(id,
                                '<strong>Available commands:</strong><br>/help<br>/status<br>/node {id}'
                            );
                            return;
                        }

                        if (cmd === '/status') {
                            try {
                                const res = await fetch('/api/status');
                                const data = await res.text();
                                await this.typewriter(id, data);
                            } catch (e) {
                                await this.typewriter(id,
                                    '<strong>Error contacting /status</strong>');
                            }
                            return;
                        }

                        // /node {id}
                        if (cmd.startsWith('/node ')) {
                            const parts = cmd.split(' ');
                            const nodeId = parts[1];

                            if (!nodeId || isNaN(nodeId)) {
                                await this.typewriter(id, 'Invalid node ID. Usage: /node 123');
                                return;
                            }

                            try {
                                const res = await fetch(`/api/nodes/${nodeId}`);
                                if (!res.ok) throw new Error();

                                const node = await res.json();

                                const text =
                                    `[Database Entry]
ID: ${node.id}
Topic: ${node.topic}
Description: ${node.description || '[no description]'}
Number of Children: ${node.children.length}
Number of Linked Ideas: ${node.strange_ideas.length}

You can reference this entry by saying "Node ${node.id}".`;

                                await this.typewriter(id, text);

                            } catch {
                                await this.typewriter(id, 'Node not found or error in request.');
                            }
                            return;
                        }

                        if (cmd.startsWith('/bridge ')) {
                            const parts = cmd.split(' ');
                            const bridgeId = parts[1];

                            if (!bridgeId || isNaN(bridgeId)) {
                                await this.typewriter(id,
                                    'Invalid bridge ID. Usage: /bridge 12');
                                return;
                            }

                            try {
                                const res = await fetch(`/api/bridges/${bridgeId}`);
                                if (!res.ok) throw new Error();

                                const bridge = await res.json();

                                const text =
                                    `[ Semantic Bridge ]
ID: ${bridge.id}
Label: ${bridge.label}
Cosine Score: ${Number(bridge.cosine_score).toFixed(2)}

From Topic (${bridge.source.id}): ${bridge.source.topic}
To Topic   (${bridge.target.id}): ${bridge.target.topic}

Use /node ${bridge.source.id} or /node ${bridge.target.id} to explore linked nodes.`;

                                await this.typewriter(id, text);

                            } catch {
                                await this.typewriter(id,
                                    'Bridge not found or error in request.');
                            }

                            return;
                        }


                        await this.typewriter(id,
                            '<strong>Unknown command.</strong> Try <code>/help</code>.');
                        return;
                    }

                    await this.typewriter(id, '<em>Thinking...</em>', 5);

                    const context = this.history
                        .slice(-6)
                        .flatMap(entry => [
                            ...(entry.command ? [{
                                role: 'user',
                                content: entry.command
                            }] : []),
                            ...(entry.response ? [{
                                role: 'assistant',
                                content: entry.response.replace(/<[^>]+>/g, '')
                            }] : [])
                        ]);


                    try {
                        const res = await fetch('/api/chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                message: cmd,
                                context: context
                            })
                        });

                        if (!res.ok) {
                            const error = await res.json();

                            if (res.status === 401 && error.code === 'WALLET_NOT_CONNECTED') {
                                await this.typewriter(id,
                                    'You must connect your wallet to use the terminal.');
                            } else {
                                await this.typewriter(id,
                                    `<strong>Error: ${error.error || 'Unknown response error.'}</strong>`
                                );
                            }

                            return;
                        }

                        const data = await res.json();

                        if (data.response) {
                            await this.typewriter(id, data.response, 10);
                        } else {
                            await this.typewriter(id, '<strong>Error getting response.</strong>');
                        }

                    } catch (e) {
                        await this.typewriter(id, '<strong>Error contacting server.</strong>');
                    }


                    this.scrollToBottom();
                }

            }));
        });
    </script>



</div>
